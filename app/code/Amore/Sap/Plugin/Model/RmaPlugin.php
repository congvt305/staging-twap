<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-07-14
 * Time: 오전 10:09
 */

namespace Amore\Sap\Plugin\Model;

use Amore\Sap\Exception\RmaSapException;
use Amore\Sap\Exception\RmaTrackNoException;
use Amore\Sap\Logger\Logger;
use CJ\Middleware\Model\SapRequest;
use Amore\Sap\Model\SapOrder\SapOrderReturnData;
use Amore\Sap\Model\Source\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Rma\Model\Rma;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use CJ\Middleware\Helper\Data as MiddlewareHelper;
use Magento\Rma\Model\Rma\RmaDataMapper;
use Magento\Rma\Model\Rma\Source\Status;
use Magento\Rma\Model\ResourceModel\Item\CollectionFactory as RmaItemCollectionFactory;

class RmaPlugin
{
    const RMA_SENT_TO_SAP_BEFORE = 0;

    const RMA_SENT_TO_SAP_SUCCESS = 1;

    const RMA_SENT_TO_SAP_FAIL = 2;

    const RMA_RESENT_TO_SAP_SUCCESS = 3;

    /**
     * @var Config
     */
    private $config;
    /**
     * @var SapRequest
     */
    private $request;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SapOrderReturnData
     */
    private $sapOrderReturnData;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var MiddlewareHelper
     */
    protected $middlewareHelper;

    /**
     * @var RmaDataMapper
     */
    private $rmaDataMaper;

    /**
     * @var Status
     */
    private $rmaSourceStatus;

    /**
     * @var RmaItemCollectionFactory
     */
    private $rmaItemCollectionFactory;


    /**
     * @param SapRequest $request
     * @param Config $config
     * @param Logger $logger
     * @param OrderRepositoryInterface $orderRepository
     * @param SapOrderReturnData $sapOrderReturnData
     * @param ManagerInterface $messageManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param MiddlewareHelper $middlewareHelper
     * @param RmaDataMapper $rmaDataMapper
     * @param Status $rmaSourceStatus
     * @param RmaItemCollectionFactory $rmaItemCollectionFactory
     */
    public function __construct(
        SapRequest $request,
        Config $config,
        Logger $logger,
        OrderRepositoryInterface $orderRepository,
        SapOrderReturnData $sapOrderReturnData,
        ManagerInterface $messageManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        MiddlewareHelper $middlewareHelper,
        RmaDataMapper $rmaDataMapper,
        Status $rmaSourceStatus,
        RmaItemCollectionFactory $rmaItemCollectionFactory
    ) {
        $this->request = $request;
        $this->config = $config;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->sapOrderReturnData = $sapOrderReturnData;
        $this->messageManager = $messageManager;
        $this->eventManager = $eventManager;
        $this->middlewareHelper = $middlewareHelper;
        $this->rmaDataMaper = $rmaDataMapper;
        $this->rmaSourceStatus = $rmaSourceStatus;
        $this->rmaItemCollectionFactory = $rmaItemCollectionFactory;
    }

    public function beforeSaveRma(Rma $subject, $data)
    {
        $enableSapCheck = $this->config->getActiveCheck('store', $subject->getStoreId());
        $enableRmaCheck = $this->config->getRmaActiveCheck('store', $subject->getStoreId());
        $availableStatus = 'authorized';
        $order = $subject->getOrder();
        $rmaSendCheck = $order->getData('sap_return_send_check');

        //handle case bundle item is missing
        $rmaItems = $subject->getItems();
        $needToUpdateStatus = false;
        if (isset($data['items']) && count($rmaItems) > 0 && count($data['items']) != count($rmaItems)) {
            foreach ($data['items'] as $id => $rmaItem) {
                $orderItem = $subject->getOrder()->getItemById($rmaItem['order_item_id']);
                if ($orderItem->getParentItem() && $orderItem->getParentItem()->getProductType() == 'bundle') {
                    $bundleRmaItem = $this->rmaItemCollectionFactory->create()
                        ->addAttributeToSelect('*')
                        ->addFieldToFilter('order_item_id', $orderItem->getParentItem()->getItemId())
                        ->addFieldToFilter('rma_entity_id', $subject->getEntityId())
                        ->getFirstItem();
                    if ($bundleRmaItem && !array_key_exists($bundleRmaItem->getId(), $data['items'])) {
                        $needToUpdateStatus = true;
                        $bundleItem = [
                            'entity_id' => $bundleRmaItem->getId(),
                            'order_item_id' => $bundleRmaItem->getOrderItemId(),
                            'resolution' => $bundleRmaItem->getResolution(),
                            'status' => $rmaItem['status']
                        ];
                        if ($rmaItem['status'] == 'authorized') {
                            $bundleItem['qty_authorized'] = $bundleRmaItem->getQtyRequested();
                        } elseif ($rmaItem['status'] == 'returned') {
                            $bundleItem['qty_returned'] = $bundleRmaItem->getQtyRequested();
                        } elseif ($rmaItem['status'] == 'approved') {
                            $bundleItem['qty_approved'] = $bundleRmaItem->getQtyRequested();
                        }
                        $data['items'][$bundleRmaItem->getId()] = $bundleItem;
                    }
                }
            }
            if ($needToUpdateStatus) {
                $itemStatuses = $this->rmaDataMaper->combineItemStatuses($data['items'], $subject->getId());
                $rmaStatus = $this->rmaSourceStatus->getStatusByItems($itemStatuses);
                $subject->setStatus($rmaStatus)->setIsUpdate(1);
            }
        }

        if ($enableSapCheck && $enableRmaCheck) {
            if ($subject->getStatus() == $availableStatus) {
                if ($rmaSendCheck == null) {
                    $subject->setData('sap_return_send_check', self::RMA_SENT_TO_SAP_BEFORE);
                }

                try {
                    $orderRmaData = $this->sapOrderReturnData->singleOrderData($subject);

                    if ($this->config->getLoggingCheck()) {
                        $this->logger->info("Order RMA Send Data");
                        $this->logger->info($this->middlewareHelper->serializeData($orderRmaData));
                    }

                    $result = $this->request->sendRequest($this->middlewareHelper->serializeData($orderRmaData), $order->getStoreId(), 'confirm');

                    if ($this->config->getLoggingCheck()) {
                        $this->logger->info("Order RMA Result Data");
                        $this->logger->info($this->middlewareHelper->serializeData($result));
                    }

                    $this->eventManager->dispatch(
                        \Amore\CustomerRegistration\Model\POSSystem::EGUANA_BIZCONNECT_OPERATION_PROCESSED,
                        [
                            'topic_name' => 'amore.sap.return.request',
                            'direction' => 'outgoing',
                            'to' => "SAP",
                            'serialized_data' => $this->middlewareHelper->serializeData($orderRmaData),
                            'status' => 1,
                            'result_message' => $this->middlewareHelper->serializeData($result)
                        ]
                    );
                    $responseHandled = $this->request->handleResponse($result);
                    if ($responseHandled === null) {
                        throw new RmaSapException(__('Something went wrong while sending order data to SAP. No response'));
                    } else {
                        $outData = [];
                        if (isset($responseHandled['data']['output']['outdata'])){
                            $outData = $responseHandled['data']['output']['outdata'];
                        }
                        if (isset($responseHandled['success']) && $responseHandled['success'] == true) {
                            foreach ($outData as $data) {
                                if ($data['retcod'] == 'S') {
                                    if ($rmaSendCheck == 0 || $rmaSendCheck == 2) {
                                        $this->messageManager->addSuccessMessage(__("Resent Return Data to Sap Successfully."));
                                    } else {
                                        $this->messageManager->addSuccessMessage(__("Sent Return Data to Sap Successfully."));
                                    }
                                } else {
                                    throw new RmaSapException(
                                        __(
                                            'Error returned from SAP for RMA %1. Error code : %2. Message : %3',
                                            $subject->getIncrementId(),
                                            $data['ugcod'],
                                            $data['ugtxt']
                                        )
                                    );
                                }
                            }
                        } else {
                            throw new RmaSapException(
                                __(
                                    'Error returned from SAP for RMA %1. Message : %2',
                                    $subject->getIncrementId(),
                                    $responseHandled['message']
                                )
                            );
                        }
                    }

                    $resultSize = count($result);

                    if ($resultSize > 0) {
                        $isSuccess = false;
                        $outdata = isset($result['data']['response']['output']['outdata']) ? $result['data']['response']['output']['outdata'] : [];
                        $isMiddlewareEnabled = $this->middlewareHelper->isMiddlewareEnabled('store', $order->getStoreId());
                        if ($isMiddlewareEnabled || (!$isMiddlewareEnabled && $result['code'] == '0000')) {
                            foreach ($outdata as $data) {
                                if ($data['retcod'] == 'S') {
                                    if ($rmaSendCheck == 0 || $rmaSendCheck == 2) {
                                        $this->messageManager->addSuccessMessage(__("Resent Return Data to Sap Successfully."));
                                    } else {
                                        $this->messageManager->addSuccessMessage(__("Sent Return Data to Sap Successfully."));
                                    }
                                    $isSuccess = true;
                                }
                            }
                        }
                        if (!$isSuccess) {
                            throw new RmaSapException(
                                __(
                                    'Error returned from SAP for RMA %1. Error code : %2. Message : %3',
                                    $subject->getIncrementId(),
                                    $result['code'],
                                    $result['message']
                                )
                            );
                        }
                    }
                } catch (NoSuchEntityException $e) {
                    throw new NoSuchEntityException(__($e->getMessage()));
                } catch (RmaTrackNoException $e) {
                    throw new RmaTrackNoException(__($e->getMessage()));
                } catch (RmaSapException $e) {
                    throw new RmaSapException(__($e->getMessage()));
                } catch (LocalizedException $e) {
                    throw new LocalizedException(__($e->getMessage()));
                } catch (\Exception $e) {
                    throw new \Exception(__('SAP Return : ' . $e->getMessage()));
                }
            }
        }
    }
}
