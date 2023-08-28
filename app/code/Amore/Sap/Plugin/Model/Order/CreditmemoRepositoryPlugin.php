<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-07-07
 * Time: 오전 9:57
 */

namespace Amore\Sap\Plugin\Model\Order;

use Amore\Sap\Exception\CrditmemoException;
use Amore\Sap\Logger\Logger;
use CJ\Middleware\Model\BaseRequest as MiddlewareRequest;
use Amore\Sap\Model\SapOrder\SapOrderCancelData;
use Amore\Sap\Model\Source\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
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
     * @var MiddlewareRequest
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
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * CreditmemoRepositoryPlugin constructor.
     * @param Json $json
     * @param MiddlewareRequest $request
     * @param Logger $logger
     * @param Config $config
     * @param ManagerInterface $messageManager
     * @param SapOrderCancelData $sapOrderCancelData
     * @param OrderRepositoryInterface $orderRepository
     * @param RmaRepositoryInterface $rmaRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        Json $json,
        MiddlewareRequest $request,
        Logger $logger,
        Config $config,
        ManagerInterface $messageManager,
        SapOrderCancelData $sapOrderCancelData,
        OrderRepositoryInterface $orderRepository,
        RmaRepositoryInterface $rmaRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Event\ManagerInterface $eventManager
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
        $this->eventManager = $eventManager;
    }

    public function beforeSave(\Magento\Sales\Model\Order\CreditmemoRepository $subject, \Magento\Sales\Api\Data\CreditmemoInterface $entity)
    {
        $storeId = $entity->getStoreId();
        $enableSapCheck = $this->config->getActiveCheck('store', $storeId);
        $enableCreditmemoCheck = $this->config->getCreditmemoActiveCheck('store', $storeId);
        $order = $this->orderRepository->get($entity->getOrderId());
        $orderStatus = $order->getStatus();

        $availableStatus = ['sap_processing', 'sap_success'];

        if ($enableSapCheck && $enableCreditmemoCheck) {
            if (in_array($orderStatus, $availableStatus)) {
                try {
                    $entity->setData('sap_creditmemo_send_check', SapOrderCancelData::CREDITMEMO_SENT_TO_SAP_BEFORE);
                    $orderUpdateData = $this->sapOrderCancelData->singleOrderData($order->getIncrementId());

                    if ($this->config->getLoggingCheck()) {
                        $this->logger->info("Single Order Cancel Send Data");
                        $this->logger->info($this->json->serialize($orderUpdateData));
                    }

                    $sapResult = $this->request->sendRequest($this->json->serialize($orderUpdateData), $order->getStoreId(), 'cancel');

                    if ($this->config->getLoggingCheck()) {
                        $this->logger->info("Single Order Cancel Result Data");
                        $this->logger->info($this->json->serialize($sapResult));
                    }

                    $this->eventManager->dispatch(
                        \Amore\CustomerRegistration\Model\POSSystem::EGUANA_BIZCONNECT_OPERATION_PROCESSED,
                        [
                            'topic_name' => 'amore.sap.refund.request',
                            'direction' => 'outgoing',
                            'to' => "SAP",
                            'serialized_data' => $this->json->serialize($orderUpdateData),
                            'status' => 1,
                            'result_message' => $this->json->serialize($sapResult)
                        ]
                    );
                    $responseHandled = $this->request->handleResponse($sapResult, $order->getStoreId());
                    if ($responseHandled === null) {
                        throw new CrditmemoException(__('Something went wrong while sending order data to SAP. No response.'));
                    } else {
                        $responseHeader = $responseHandled['data']['header'];
                        if (isset($responseHandled['success']) && $responseHandled['success'] == true) {
                            if ($responseHeader['rtn_TYPE'] == 'S') {
                                $this->messageManager->addSuccessMessage(__('Order %1 sent to SAP Successfully.', $order->getIncrementId()));
                            } else {
                                throw new CrditmemoException(
                                    __(
                                        'Error returned from SAP for order %1. Error code : %2. Message : %3',
                                        $order->getIncrementId(),
                                        $responseHeader['rtn_TYPE'],
                                        $responseHeader['rtn_MSG']
                                    )
                                );
                            }
                        } else {
                            throw new CrditmemoException(
                                __(
                                    'Error returned from SAP for order %1. Message : %2',
                                    $order->getIncrementId(),
                                    $responseHandled['message']
                                )
                            );
                        }
                    }
                } catch (NoSuchEntityException $e) {
                    throw new NoSuchEntityException(__('SAP : ' . $e->getMessage()));
                } catch (\Exception $e) {
                    throw new \Exception(__('SAP : ' . $e->getMessage()));
                }
            }
        }
    }
}
