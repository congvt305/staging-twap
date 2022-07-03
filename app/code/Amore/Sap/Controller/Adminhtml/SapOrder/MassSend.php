<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-22
 * Time: 오후 3:48
 */

namespace Amore\Sap\Controller\Adminhtml\SapOrder;

use Amore\Sap\Exception\ShipmentNotExistException;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Amore\Sap\Model\Connection\Request;
use Amore\Sap\Model\SapOrder\SapOrderConfirmData;
use Amore\Sap\Logger\Logger;
use Amore\Sap\Model\Source\Config;
use Amore\Sap\Controller\Adminhtml\AbstractAction;
use CJ\Middleware\Helper\Data as MiddlewareHelper;

class MassSend extends AbstractAction
{
    /**
     * @var Filter
     */
    private $filter;
    /**
     * @var SapOrderConfirmData
     */
    private $sapOrderConfirmData;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param Action\Context $context
     * @param Json $json
     * @param Request $request
     * @param Logger $logger
     * @param Config $config
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param SapOrderConfirmData $sapOrderConfirmData
     * @param OrderRepositoryInterface $orderRepository
     * @param TimezoneInterface $timezoneInterface
     * @param ManagerInterface $eventManager
     * @param MiddlewareHelper $middlewareHelper
     */
    public function __construct(
        Action\Context $context,
        Json $json,
        Request $request,
        Logger $logger,
        Config $config,
        Filter $filter,
        CollectionFactory $collectionFactory,
        SapOrderConfirmData $sapOrderConfirmData,
        OrderRepositoryInterface $orderRepository,
        TimezoneInterface $timezoneInterface,
        ManagerInterface $eventManager,
        MiddlewareHelper $middlewareHelper
    ) {
        parent::__construct($context, $json, $request, $logger, $config, $middlewareHelper);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->sapOrderConfirmData = $sapOrderConfirmData;
        $this->orderRepository = $orderRepository;
        $this->timezoneInterface = $timezoneInterface;
        $this->eventManager = $eventManager;
    }

    public function execute()
    {
        $orderDataList = [];
        $orderItemDataList = [];
        $orderStatusError = [];
        $storeIdList = [];

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $collection = $this->filter->getCollection($this->collectionFactory->create());

        /** @var \Magento\Sales\Model\Order $order */
        foreach ($collection->getItems() as $order) {
            try {
                $storeId = $order->getStoreId();
                $storeIdList[] = $storeId;
                if ($order->getStatus() == 'processing' || $order->getStatus() == 'sap_fail' || $order->getStatus() == 'processing_with_shipment') {
                    $orderData = $this->sapOrderConfirmData->getOrderData($order->getIncrementId());
                    $orderItemData = $this->sapOrderConfirmData->getOrderItem($order->getIncrementId());

                    if (isset($orderData[0])) {
                        $this->sapOrderConfirmData->setReturnOrderData(
                            $orderData[0],
                            $order
                        );
                    }
                    if (isset($orderItemData[0])) {
                        $orderItemData = $this->sapOrderConfirmData->setReturnItemOrderData(
                            $orderItemData,
                            $order
                        );
                    }
                    $this->orderRepository->save($order);

                    $orderDataList = array_merge($orderDataList, $orderData);
                    $orderItemDataList = array_merge($orderItemDataList, $orderItemData);
                } else {
                    $orderStatusError[] = $order->getIncrementId();
                }
            } catch (ShipmentNotExistException $e) {
                $orderStatusError[] = $order->getIncrementId() . ' : ' . $e->getMessage();
                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("MASS ORDER DATA NO SHIPMENT EXCEPTION");
                    $this->logger->info($order->getIncrementId() . ' : ' . $e->getMessage());
                }
            } catch (NoSuchEntityException $e) {
                $orderStatusError[] = $order->getIncrementId() . ' : ' . $e->getMessage();
                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("MASS ORDER DATA NO SUCH ENTITY EXCEPTION");
                    $this->logger->info($order->getIncrementId() . ' : ' . $e->getMessage());
                }
            } catch (\Exception $e) {
                $orderStatusError[] = $order->getIncrementId() . ' : ' . $e->getMessage();
                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("MASS ORDER DATA EXCEPTION");
                    $this->logger->info($order->getIncrementId() . ' : ' . $e->getMessage());
                }
            }
        }

        if ($this->config->getLoggingCheck()) {
            $this->logger->info("ORDER List Data");
            $this->logger->info($this->json->serialize($orderDataList));
            $this->logger->info("ORDER Item List Data");
            $this->logger->info($this->json->serialize($orderItemDataList));
        }

        if ($this->differentStoreExist($storeIdList)) {
            $this->messageManager->addErrorMessage(__("There are more than two different stores` orders selected. Please select order by store and try again."));
            return $resultRedirect->setPath('sales/order/index');
        }

        if (count($orderStatusError) > 0) {
            foreach ($orderStatusError as $error) {
                $this->messageManager->addErrorMessage(__("Error occurred while sending order : %1.", $error));
            }
        }

        try {
            $orderCount = count($orderDataList);
            if ($orderCount > 0) {
                $storeIdUnique = array_unique($storeIdList);
                $sendData = $this->sapOrderConfirmData->massSendOrderData($orderDataList, $orderItemDataList);
                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("ORDER MASS SEND DATA");
                    $this->logger->info($this->json->serialize($sendData));
                }

                $result = $this->request->postRequest($this->json->serialize($sendData), array_shift($storeIdUnique));

                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("ORDER MASS SEND RESULT");
                    $this->logger->info($this->json->serialize($result));
                }

                $this->eventManager->dispatch(
                    "eguana_bizconnect_operation_processed",
                    [
                        'topic_name' => 'amore.sap.order.masssend.request',
                        'direction' => 'outgoing',
                        'to' => "SAP",
                        'serialized_data' => $this->json->serialize($sendData),
                        'status' => empty($orderStatusError) ? 1 : 0,
                        'result_message' => $this->json->serialize($result) . "\n" . "Error Orders : " . $this->json->serialize($orderStatusError)
                    ]
                );

                $responseHandled = $this->request->handleResponse($result, array_shift($storeIdUnique));
                if ($responseHandled === null) {
                    $this->messageManager->addErrorMessage(__('Something went wrong while sending order data to SAP. No response'));
                } else {
                    $outData = $responseHandled['data']['output']['outdata'];
                    if (isset($responseHandled['success']) && $responseHandled['success'] == true) {
                        $ordersSucceeded = [];
                        $orderFailed = [];
                        foreach ($outData as $data) {
                            if ($data['retcod'] == 'S') {
                                $ordersSucceeded[] = $this->getOriginOrderIncrementId($data);
                                $succeededOrderObject = $this->sapOrderConfirmData->getOrderInfo($data['odrno']);
                                $orderSendCheck = $succeededOrderObject->getData('sap_order_send_check');
                                $succeededOrderObject->setStatus('sap_processing');

                                if ($orderSendCheck == 0 || $orderSendCheck == 2) {
                                    $succeededOrderObject->setData('sap_order_send_check', SapOrderConfirmData::ORDER_RESENT_TO_SAP_SUCCESS);
                                } else {
                                    $succeededOrderObject->setData('sap_order_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_SUCCESS);
                                }

                                $this->orderRepository->save($succeededOrderObject);
                            } else {
                                $error = ['increment_id' => $data['odrno'], 'code' => $data['ugcod'], 'error_code' => $data['ugtxt']];
                                array_push($orderFailed, $error);
                                $this->changeOrderSendCheckValue($data, SapOrderConfirmData::ORDER_SENT_TO_SAP_FAIL, "sap_fail");
                                $this->messageManager->addErrorMessage(
                                    __(
                                        'Error returned from SAP for order %1. Error code : %2. Message : %3',
                                        $data['odrno'],
                                        $data['ugcod'],
                                        $data['ugtxt']
                                    )
                                );
                            }
                        }
                        $countOrderSucceeded = count($ordersSucceeded);
                        if ($countOrderSucceeded > 0) {
                            $this->messageManager->addSuccessMessage(__('%1 orders sent to SAP Successfully.', $countOrderSucceeded));
                        }

                        if (count($orderFailed)) {
                            $this->eventManager->dispatch(
                                "eguana_bizconnect_operation_processed",
                                [
                                    'topic_name' => 'amore.sap.order.masssend.failed',
                                    'direction' => 'outgoing',
                                    'to' => "SAP",
                                    'serialized_data' => $this->json->serialize($sendData),
                                    'status' => 0,
                                    'result_message' => $this->json->serialize($orderFailed)
                                ]
                            );
                        }
                    } else {
                        foreach ($outData as $data) {
                            $this->changeOrderSendCheckValue($data, SapOrderConfirmData::ORDER_SENT_TO_SAP_FAIL, "sap_fail");
                        }
                        $this->messageManager->addErrorMessage(
                            __(
                                'Error returned from SAP for order %1. Message : %2',
                                $order->getIncrementId(),
                                $responseHandled['message']
                            )
                        );
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__('There is no order to send. Check order and try again.'));
            }
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }

        return $resultRedirect->setPath('sales/order/index');
    }

    public function getOrderIncrementId($incrementId, $orderSendCheck)
    {
        if (is_null($orderSendCheck)) {
            $incrementIdForSap = $orderSendCheck;
        } elseif ($orderSendCheck == 0 || $orderSendCheck == 2) {
            $currentDate = $this->timezoneInterface->date()->format('ymdHis');
            $incrementIdForSap = $incrementId . '_' . $currentDate;
        } else {
            $incrementIdForSap = $incrementId;
        }
        return $incrementIdForSap;
    }

    public function getOriginOrderIncrementId($data)
    {
        if (strpos($data['odrno'], '_')) {
            list($incrementId, $date) = explode('_', $data['odrno']);
        } else {
            $incrementId = $data['odrno'];
        }
        return $incrementId;
    }

    public function differentStoreExist($storeIdList)
    {
        if (count(array_unique($storeIdList)) > 1) {
            return true;
        }
        return false;
    }

    public function changeOrderSendCheckValue($data, $sapSendCheckStatus, $orderStatus)
    {
        $orderIncrementId = $this->getOriginOrderIncrementId($data);
        $order = $this->sapOrderConfirmData->getOrderInfo($orderIncrementId);
        $order->setData('sap_order_send_check', $sapSendCheckStatus);
        $order->setStatus($orderStatus);
        $this->orderRepository->save($order);
    }
}
