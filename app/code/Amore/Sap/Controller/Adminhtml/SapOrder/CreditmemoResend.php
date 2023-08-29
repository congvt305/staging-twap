<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-07-17
 * Time: 오후 4:14
 */

namespace Amore\Sap\Controller\Adminhtml\SapOrder;

use Amore\Sap\Logger\Logger;
use CJ\Middleware\Model\SapRequest;
use Amore\Sap\Model\SapOrder\SapOrderCancelData;
use Amore\Sap\Model\Source\Config;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;

class CreditmemoResend extends Action
{
    /**
     * @var SapOrderCancelData
     */
    private $sapOrderCancelData;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SapRequest
     */
    private $request;

    /**
     * @param Action\Context $context
     * @param Json $json
     * @param SapRequest $request
     * @param Logger $logger
     * @param Config $config
     * @param SapOrderCancelData $sapOrderCancelData
     * @param OrderRepositoryInterface $orderRepository
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Action\Context $context,
        Json $json,
        SapRequest $request,
        Logger $logger,
        Config $config,
        SapOrderCancelData $sapOrderCancelData,
        OrderRepositoryInterface $orderRepository,
        ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->json = $json;
        $this->request = $request;
        $this->logger = $logger;
        $this->config = $config;
        $this->sapOrderCancelData = $sapOrderCancelData;
        $this->orderRepository = $orderRepository;
        $this->resourceConnection = $resourceConnection;
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

        if ($enableCheck) {
            try {
                $orderUpdateData = $this->sapOrderCancelData->singleOrderData($order->getIncrementId());

                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("Single Order Cancel Resend Data");
                    $this->logger->info($this->json->serialize($orderUpdateData));
                }

                $sapResult = $this->request->sendRequest($orderUpdateData, $order->getStoreId(), 'cancel');

                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("Single Order Cancel Resend Result Data");
                    $this->logger->info($this->json->serialize($sapResult));
                }
                $responseHandled = $this->request->handleResponse($sapResult);
                if ($responseHandled === null) {
                    $this->updateCreditmemoSapSendCheck($creditMemoId, SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_FAIL);
                    $this->messageManager->addErrorMessage(__('Something went wrong while sending order data to SAP. No response.'));
                } else {
                    $responseHeader = $responseHandled['data']['header'];
                    if ($responseHandled['success'] === false) {
                        $this->updateCreditmemoSapSendCheck($creditMemoId, SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_FAIL);
                        $this->messageManager->addErrorMessage(
                            __(
                                'Error returned from SAP for order %1. Error code : %2. Message : %3',
                                $order->getIncrementId(),
                                $responseHeader['rtn_TYPE'], $responseHeader['rtn_MSG']
                            )
                        );
                    } else {
                        if ($responseHeader['rtn_TYPE'] == 'S') {
                            try {
                                $this->updateCreditmemoSapSendCheck($creditMemoId, SapOrderCancelData::CREDITMEMO_RESENT_TO_SAP_SUCCESS);
                                $this->messageManager->addSuccessMessage(__('Order %1 sent to SAP Successfully.', $order->getIncrementId()));
                            } catch (\Exception $exception) {
                                $this->updateCreditmemoSapSendCheck($creditMemoId, SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_FAIL);
                                $this->messageManager->addErrorMessage(
                                    __(
                                        'Something went wrong while saving order %1. Message : %2',
                                        $order->getIncrementId(),
                                        $exception->getMessage()
                                    )
                                );
                            }
                        } else {
                            $this->updateCreditmemoSapSendCheck($creditMemoId, SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_FAIL);
                            $this->messageManager->addErrorMessage(
                                __(
                                    'Error returned from SAP for order %1. Error code : %2. Message : %3',
                                    $order->getIncrementId(),
                                    $responseHeader['rtn_TYPE'],
                                    $responseHeader['rtn_MSG']
                                )
                            );
                        }
                    }
                }
            } catch (NoSuchEntityException $e) {
                $this->updateCreditmemoSapSendCheck($creditMemoId, SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_FAIL);
                $this->messageManager->addErrorMessage(__('SAP : ' . $e->getMessage()));
            } catch (\Exception $e) {
                $this->updateCreditmemoSapSendCheck($creditMemoId, SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_FAIL);
                $this->messageManager->addErrorMessage(__('SAP : ' . $e->getMessage()));
            }
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('sales/order_creditmemo/view', ['creditmemo_id' => $creditMemoId]);

        return $resultRedirect;
    }

    public function updateCreditmemoSapSendCheck($creditmemoId, $value)
    {
        $tableName = $this->resourceConnection->getTableName('sales_creditmemo');
        $connection = $this->resourceConnection->getConnection();
        $connection->update($tableName, ['sap_creditmemo_send_check' => $value], ['entity_id = ?' => $creditmemoId]);
    }
}
