<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-23
 * Time: 오후 3:00
 */

namespace Amore\Sap\Controller\Adminhtml\SapOrder;

use Amore\Sap\Exception\ShipmentNotExistException;
use Amore\Sap\Model\Connection\Request;
use Amore\Sap\Model\SapOrder\SapOrderConfirmData;
use Amore\Sap\Model\Source\Config;
use Amore\Sap\Logger\Logger;
use Amore\Sap\Controller\Adminhtml\AbstractAction;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderSend extends AbstractAction
{
    /**
     * @var SapOrderConfirmData
     */
    private $sapOrderConfirmData;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var ManagerInterface
     */
    private $eventManager;


    /**
     * OrderSend constructor.
     * @param Action\Context $context
     * @param Json $json
     * @param Request $request
     * @param Logger $logger
     * @param Config $config
     * @param OrderRepositoryInterface $orderRepository
     * @param SapOrderConfirmData $sapOrderConfirmData
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Action\Context $context,
        Json $json,
        Request $request,
        Logger $logger,
        Config $config,
        OrderRepositoryInterface $orderRepository,
        SapOrderConfirmData $sapOrderConfirmData,
        ManagerInterface $eventManager
    ) {
        parent::__construct($context, $json, $request, $logger, $config);
        $this->orderRepository = $orderRepository;
        $this->sapOrderConfirmData = $sapOrderConfirmData;
        $this->eventManager = $eventManager;
    }

    public function execute()
    {
        if (!$this->config->checkTestMode()) {
            if ($this->config->getLoggingCheck()) {
                $this->logger->info("SAP Send Order Entity Id");
                $this->logger->info($this->getRequest()->getParam('order_id'));
            }
            $orderId = $this->getRequest()->getParam('order_id');
            $order = $this->orderRepository->get($orderId);
            $orderSendCheck = $order->getData('sap_order_send_check');

            if ($orderSendCheck == null) {
                $order->setData('sap_order_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_BEFORE);
            }

            try {
                $orderSendData = $this->sapOrderConfirmData->singleOrderData($order->getIncrementId());

                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("Single Order Send Data");
                    $this->logger->info($this->json->serialize($orderSendData));
                }

                $result = $this->request->postRequest($this->json->serialize($orderSendData), $order->getStoreId());

                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("Single Order Result Data");
                    $this->logger->info($this->json->serialize($result));
                }

                $this->eventManager->dispatch(
                    "eguana_bizconnect_operation_processed",
                    [
                        'topic_name' => 'amore.sap.order.send.request',
                        'direction' => 'outgoing',
                        'to' => "SAP",
                        'serialized_data' => $this->json->serialize($orderSendData),
                        'status' => 1,
                        'result_message' => $this->json->serialize($result)
                    ]
                );

                $resultSize = count($result);

                if ($resultSize > 0) {
                    if ($result['code'] == '0000') {
                        $outdata = $result['data']['response']['output']['outdata'];
                        foreach ($outdata as $data) {
                            if ($data['retcod'] == 'S') {
                                $order->setStatus('sap_processing');
                                if ($orderSendCheck == 0 || $orderSendCheck == 2) {
                                    $order->setData('sap_order_send_check', SapOrderConfirmData::ORDER_RESENT_TO_SAP_SUCCESS);
                                } else {
                                    $order->setData('sap_order_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_SUCCESS);
                                }
                                $this->messageManager->addSuccessMessage(__('Order %1 sent to SAP Successfully.', $order->getIncrementId()));
                            } else {
                                $order->setData('sap_order_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_FAIL);
                                $this->messageManager->addErrorMessage(
                                    __(
                                        'Error returned from SAP for order %1. Error code : %2. Message : %3',
                                        $order->getIncrementId(),
                                        $data['ugcod'],
                                        $data['ugtxt']
                                    )
                                );
                            }
                        }
                    } else {
                        $order->setData('sap_order_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_FAIL);
                        $this->messageManager->addErrorMessage(
                            __(
                                'Error returned from SAP for order %1. Error code : %2. Message : %3',
                                $order->getIncrementId(),
                                $result['code'],
                                $result['message']
                            )
                        );
                    }
                } else {
                    $order->setData('sap_order_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_FAIL);
                    $this->messageManager->addErrorMessage(
                        __('Something went wrong while sending order data to SAP. No response')
                    );
                }
            } catch (ShipmentNotExistException $e) {
                $order->setData('sap_order_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_FAIL);
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (NoSuchEntityException $e) {
                $order->setData('sap_order_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_FAIL);
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (LocalizedException $e) {
                $order->setData('sap_order_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_FAIL);
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $order->setData('sap_order_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_FAIL);
                $this->messageManager->addErrorMessage($e->getMessage());
            }
            $this->orderRepository->save($order);
        } else {
            try {
                $testOrderData = $this->sapOrderConfirmData->getTestOrderConfirm();

                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("Single Test Order Send Data");
                    $this->logger->info($this->json->serialize($testOrderData));
                }

                $serializedTestData = $this->json->serialize($testOrderData);

                $result = $this->request->postRequest($serializedTestData, 0, 'confirm');

                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("Single Test Order Result Data");
                    $this->logger->info($this->json->serialize($result));
                }

                $resultSize = count($result);
                if ($resultSize > 0) {
                    if ($result['code'] == '0000') {
                        $outdata = $result['data']['response']['output']['outdata'];
                        foreach ($outdata as $data) {
                            if ($data['retcod'] == 'S') {
                                $this->messageManager->addSuccessMessage(__('Test Order sent to SAP Successfully.'));
                            } else {
                                $this->messageManager->addErrorMessage(
                                    __(
                                        'Error occurred while sending order. Error code : %1. Message : %2',
                                        $outdata['ugcod'],
                                        $outdata['ugtxt']
                                    )
                                );
                            }
                        }
                    } else {
                        $this->messageManager->addErrorMessage(
                            __(
                                'Error occurred while sending order. Error code : %2. Message : %3',
                                $result['code'],
                                $result['message']
                            )
                        );
                    }
                } else {
                    $this->messageManager->addErrorMessage(
                        __('Something went wrong while sending Test order data to SAP. No response')
                    );
                }

            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            }
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('sales/order/index');

        return $resultRedirect;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amore_Sap::sap');
    }
}
