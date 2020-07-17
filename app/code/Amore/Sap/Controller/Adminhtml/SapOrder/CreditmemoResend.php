<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-07-17
 * Time: 오후 4:14
 */

namespace Amore\Sap\Controller\Adminhtml\SapOrder;

use Amore\Sap\Logger\Logger;
use Amore\Sap\Model\Connection\Request;
use Amore\Sap\Model\SapOrder\SapOrderCancelData;
use Amore\Sap\Model\Source\Config;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;

class CreditmemoResend extends Action
{
    /**
     * @var Json
     */
    private $json;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var SapOrderCancelData
     */
    private $sapOrderCancelData;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * CreditmemoResend constructor.
     * @param Action\Context $context
     * @param Json $json
     * @param Request $request
     * @param Logger $logger
     * @param Config $config
     * @param SapOrderCancelData $sapOrderCancelData
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Action\Context $context,
        Json $json,
        Request $request,
        Logger $logger,
        Config $config,
        SapOrderCancelData $sapOrderCancelData,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->json = $json;
        $this->request = $request;
        $this->logger = $logger;
        $this->config = $config;
        $this->sapOrderCancelData = $sapOrderCancelData;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }


    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $creditMemoId = $this->getRequest()->getParam('creditmemo_id');
        $order = $this->orderRepository->get($orderId);
        $enableCheck = $this->config->getActiveCheck('store', $order->getStoreId());

        if ($this->config->getLoggingCheck()) {
            $this->logger->info("CREDITMEMO RESEND - Order Entity Id");
            $this->logger->info($orderId);
            $this->logger->info("CREDITMEMO RESEND - CreditMemo Entity Id");
            $this->logger->info($creditMemoId);
        }
        $order->setData('sap_creditmemo_send_check', SapOrderCancelData::CREDITMEMO_RESENT_TO_SAP_SUCCESS);
        $this->orderRepository->save($order);
        if ($enableCheck) {
            if (!$this->config->checkTestMode()) {
                try {
                    $orderUpdateData = $this->sapOrderCancelData->singleOrderData($order->getIncrementId());

                    if ($this->config->getLoggingCheck()) {
                        $this->logger->info("Single Order Cancel Resend Data");
                        $this->logger->info($this->json->serialize($orderUpdateData));
                    }

                    $sapResult = $this->request->postRequest($this->json->serialize($orderUpdateData), $order->getStoreId(), 'cancel');

                    if ($this->config->getLoggingCheck()) {
                        $this->logger->info("Single Order Cancel Resend Result Data");
                        $this->logger->info($this->json->serialize($sapResult));
                    }

                    $resultSize = count($sapResult);
                    if ($resultSize > 0) {
                        if ($sapResult['code'] == '0000') {
                            $responseHeader = $sapResult['data']['response']['header'];
                            if ($responseHeader['rtn_TYPE'] == 'S') {
                                try {
                                    $order->setData('sap_creditmemo_send_check', SapOrderCancelData::CREDITMEMO_RESENT_TO_SAP_SUCCESS);
                                    $this->messageManager->addSuccessMessage(__('Order %1 sent to SAP Successfully.', $order->getIncrementId()));
                                } catch (\Exception $exception) {
                                    $order->setData('sap_creditmemo_send_check', SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_FAIL);
                                    $this->messageManager->addErrorMessage(__('Something went wrong while saving order %1. Message : %2', $order->getIncrementId(),$exception->getMessage()));
                                }
                            } else {
                                $order->setData('sap_creditmemo_send_check', SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_FAIL);
                                $this->messageManager->addErrorMessage(__('Error returned from SAP for order %1. Error code : %2. Message : %3', $order->getIncrementId(), $responseHeader['rtn_TYPE'], $responseHeader['rtn_MSG']));
                            }
                        } else {
                            $order->setData('sap_creditmemo_send_check', SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_FAIL);
                            $this->messageManager->addErrorMessage(__('Error returned from SAP for order %1. Error code : %2. Message : %3', $order->getIncrementId(), $sapResult['code'], $sapResult['message']));
                        }
                    } else {
                        $order->setData('sap_creditmemo_send_check', SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_FAIL);
                        $this->messageManager->addErrorMessage(__('Something went wrong while sending order data to SAP. No response.'));
                    }
                } catch (NoSuchEntityException $e) {
                    $order->setData('sap_creditmemo_send_check', SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_FAIL);
                    $this->messageManager->addErrorMessage(__('SAP : ' . $e->getMessage()));
                } catch (\Exception $e) {
                    $order->setData('sap_creditmemo_send_check', SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_FAIL);
                    $this->messageManager->addErrorMessage(__('SAP : ' . $e->getMessage()));
                }
                $this->orderRepository->save($order);
            }
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('sales/order_creditmemo/view', ['creditmemo_id' => $creditMemoId]);

        return $resultRedirect;
    }
}
