<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-07-07
 * Time: 오전 9:57
 */

namespace Amore\Sap\Plugin\Model\Order;

use Amore\Sap\Logger\Logger;
use Amore\Sap\Model\Connection\Request;
use Amore\Sap\Model\SapOrder\SapOrderCancelData;
use Amore\Sap\Model\Source\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Rma\Api\RmaRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class CreditmemoRepositoryPlugin
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
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var SapOrderCancelData
     */
    private $sapOrderCancelData;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var RmaRepositoryInterface
     */
    private $rmaRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * CreditmemoRepositoryPlugin constructor.
     * @param Json $json
     * @param Request $request
     * @param Logger $logger
     * @param Config $config
     * @param ManagerInterface $messageManager
     * @param SapOrderCancelData $sapOrderCancelData
     * @param OrderRepositoryInterface $orderRepository
     * @param RmaRepositoryInterface $rmaRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Json $json,
        Request $request,
        Logger $logger,
        Config $config,
        ManagerInterface $messageManager,
        SapOrderCancelData $sapOrderCancelData,
        OrderRepositoryInterface $orderRepository,
        RmaRepositoryInterface $rmaRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->json = $json;
        $this->request = $request;
        $this->logger = $logger;
        $this->config = $config;
        $this->messageManager = $messageManager;
        $this->sapOrderCancelData = $sapOrderCancelData;
        $this->orderRepository = $orderRepository;
        $this->rmaRepository = $rmaRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function afterSave(\Magento\Sales\Model\Order\CreditmemoRepository $subject, $result, \Magento\Sales\Api\Data\CreditmemoInterface $entity)
    {
        $storeId = $result->getStoreId();
        $enableSapCheck = $this->config->getActiveCheck('store', $storeId);
        $enableCreditmemoCheck = $this->config->getCreditmemoActiveCheck('store', $storeId);
        $order = $this->orderRepository->get($result->getOrderId());
        $orderStatus = $order->getStatus();

        $rma = $this->getRma($order->getEntityId());

        $availableStatus = ['complete', 'sap_processing', 'shipment_processing', 'preparing'];
        $creditMemoOrder = $entity->getOrder();
        $creditMemoOrder->setData('sap_creditmemo_send_check', SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_BEFORE);

        if ($enableSapCheck && $enableCreditmemoCheck) {
            if (in_array($orderStatus, $availableStatus)) {
                if (!$this->config->checkTestMode()) {
                    try {
                        $orderUpdateData = $this->sapOrderCancelData->singleOrderData($order->getIncrementId());

                        if ($this->config->getLoggingCheck()) {
                            $this->logger->info("Single Order Cancel Send Data");
                            $this->logger->info($this->json->serialize($orderUpdateData));
                        }

                        $sapResult = $this->request->postRequest($this->json->serialize($orderUpdateData), $order->getStoreId(), 'cancel');

                        if ($this->config->getLoggingCheck()) {
                            $this->logger->info("Single Order Cancel Result Data");
                            $this->logger->info($this->json->serialize($sapResult));
                        }

                        $resultSize = count($sapResult);
                        if ($resultSize > 0) {
                            if ($sapResult['code'] == '0000') {
                                $responseHeader = $sapResult['data']['response']['header'];
                                if ($responseHeader['rtn_TYPE'] == 'S') {
                                    try {
                                        $creditMemoOrder->setData('sap_creditmemo_send_check', SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_SUCCESS);
                                        $this->messageManager->addSuccessMessage(__('Order %1 sent to SAP Successfully.', $order->getIncrementId()));
                                    } catch (\Exception $exception) {
                                        $creditMemoOrder->setData('sap_creditmemo_send_check', SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_FAIL);
                                        $this->messageManager->addErrorMessage(__('Something went wrong while saving order %1. Message : %2', $order->getIncrementId(), $exception->getMessage()));
                                    }
                                } else {
                                    $creditMemoOrder->setData('sap_creditmemo_send_check', SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_FAIL);
//                                    throw new \Exception(__('Error returned from SAP for order %1. Error code : %2. Message : %3', $order->getIncrementId(), $responseHeader['rtn_TYPE'], $responseHeader['rtn_MSG']));
                                    $this->messageManager->addErrorMessage(__('Error returned from SAP for order %1. Error code : %2. Message : %3', $order->getIncrementId(), $responseHeader['rtn_TYPE'], $responseHeader['rtn_MSG']));
                                }
                            } else {
                                $creditMemoOrder->setData('sap_creditmemo_send_check', SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_FAIL);
//                                throw new \Exception(__('Error returned from SAP for order %1. Error code : %2. Message : %3', $order->getIncrementId(), $result['code'], $result['message']));
                                $this->messageManager->addErrorMessage(__('Error returned from SAP for order %1. Error code : %2. Message : %3', $order->getIncrementId(), $sapResult['code'], $sapResult['message']));
                            }
                        } else {
                            $creditMemoOrder->setData('sap_creditmemo_send_check', SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_FAIL);
//                            throw new \Exception(__('Something went wrong while sending order data to SAP. No response.'));
                            $this->messageManager->addErrorMessage(__('Something went wrong while sending order data to SAP. No response.'));
                        }
                    } catch (NoSuchEntityException $e) {
                        $creditMemoOrder->setData('sap_creditmemo_send_check', SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_FAIL);
//                        throw new NoSuchEntityException(__('SAP : ' . $e->getMessage()));
                        $this->messageManager->addErrorMessage(__('SAP : ' . $e->getMessage()));
                    } catch (\Exception $e) {
                        $creditMemoOrder->setData('sap_creditmemo_send_check', SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_FAIL);
//                        throw new \Exception(__('SAP : ' . $e->getMessage()));
                        $this->messageManager->addErrorMessage(__('SAP : ' . $e->getMessage()));
                    }
                } else {
                    $testData = $this->sapOrderCancelData->getTestCancelOrder();

                    $jsonTestData = $this->json->serialize($testData);

                    if ($this->config->getLoggingCheck()) {
                        $this->logger->info("Single Test Order Cancel Send Data");
                        $this->logger->info($jsonTestData);
                    }

                    try {
                        $sapResult = $this->request->postRequest($jsonTestData, 0, 'cancel');

                        if ($this->config->getLoggingCheck()) {
                            $this->logger->info("Single Order Test Cancel Result Data");
                            $this->logger->info($this->json->serialize($sapResult));
                        }

                        $resultSize = count($result);

                        if ($resultSize > 0) {
                            if ($sapResult['code'] == '0000') {
                                $responseHeader = $sapResult['data']['response']['header'];
                                if ($responseHeader['rtn_TYPE'] == 'S') {
                                    $this->messageManager->addSuccessMessage(__('Test Order Address Update sent to SAP Successfully.'));
                                } else {
                                    throw new \Exception(__('Error returned from SAP for Test order. Error code : %1. Message : %2', $responseHeader['rtn_TYPE'], $responseHeader['rtn_MSG']));
                                }
                            } else {
                                throw new \Exception(__('Error returned from SAP for Test order. Error code : %1. Message : %2', $sapResult['code'], $sapResult['message']));
                            }
                        } else {
                            throw new \Exception(__('Something went wrong while sending order data to SAP. No response.'));
                        }
                    } catch (LocalizedException $e) {
                        throw new NoSuchEntityException(__('SAP : ' . $e->getMessage()));
                    } catch (\Exception $e) {
                        throw new \Exception(__('SAP : ' . $e->getMessage()));
                    }
                }
            }
        }
        return $result;
    }

    public function getRma($orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId, 'eq')
            ->addFilter('status', 'processed_closed', 'eq')
            ->create();

        $rma = $this->rmaRepository->getList($searchCriteria)->getItems();
        $rmaCount = $this->rmaRepository->getList($searchCriteria)->getTotalCount();

        if ($rmaCount >= 1) {
            return reset($rma);
        } else {
            return null;
        }
    }

}
