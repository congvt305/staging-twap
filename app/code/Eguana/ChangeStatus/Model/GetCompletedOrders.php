<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: brian
 * Date: 2020/07/14
 * Time: 10:24 AM
 */

namespace Eguana\ChangeStatus\Model;

use Amore\PointsIntegration\Logger\Logger;
use Eguana\ChangeStatus\Model\Source\Config;
use Eguana\GWLogistics\Model\ResourceModel\StatusNotification\CollectionFactory as NotificationCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class GetCompletedOrders
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var TimezoneInterface
     */
    private $timezone;
    /**
     * @var DateTime
     */
    private $dateTime;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var \Amore\PointsIntegration\Model\PosOrderData
     */
    private $posOrderData;
    /**
     * @var \Amore\PointsIntegration\Model\Connection\Request
     */
    private $request;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var \Amore\PointsIntegration\Model\Source\Config
     */
    private $PointsIntegrationConfig;
    /**
     * @var Logger
     */
    private $pointsIntegrationLogger;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var NotificationCollectionFactory
     */
    private $statusNotificationCollection;

    /**
     * GetCompletedOrders constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param DateTime $dateTime
     * @param Config $config
     * @param StoreManagerInterface $storeManagerInterface
     * @param LoggerInterface $logger
     * @param TimezoneInterface $timezone
     * @param \Amore\PointsIntegration\Model\PosOrderData $posOrderData
     * @param \Amore\PointsIntegration\Model\Connection\Request $request
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Json $json
     * @param \Amore\PointsIntegration\Model\Source\Config $PointsIntegrationConfig
     * @param Logger $pointsIntegrationLogger
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param NotificationCollectionFactory $statusNotificationCollection
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        DateTime $dateTime,
        Config $config,
        StoreManagerInterface $storeManagerInterface,
        LoggerInterface $logger,
        TimezoneInterface $timezone,
        \Amore\PointsIntegration\Model\PosOrderData $posOrderData,
        \Amore\PointsIntegration\Model\Connection\Request $request,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Json $json,
        \Amore\PointsIntegration\Model\Source\Config $PointsIntegrationConfig,
        Logger $pointsIntegrationLogger,
        OrderCollectionFactory $orderCollectionFactory,
        NotificationCollectionFactory $statusNotificationCollection
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->dateTime = $dateTime;
        $this->config = $config;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->logger = $logger;
        $this->timezone = $timezone;
        $this->posOrderData = $posOrderData;
        $this->request = $request;
        $this->eventManager = $eventManager;
        $this->json = $json;
        $this->PointsIntegrationConfig = $PointsIntegrationConfig;
        $this->pointsIntegrationLogger = $pointsIntegrationLogger;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->statusNotificationCollection = $statusNotificationCollection;
    }

    public function getCompletedOrder($storeId)
    {
        $toBeCompletedDays = is_null($this->config->getAvailableReturnDays($storeId)) ? 7 : $this->config->getAvailableReturnDays($storeId);
        $gmtDate = $this->dateTime->gmtDate();
        $timezone = $this->timezone->getConfigTimezone('store', $storeId);
        $storeTime = $this->timezone->formatDateTime($gmtDate, 3, 3, null, $timezone);
        $currentTimezoneDate = date('Y-m-d H:i:s', strtotime($storeTime));
        $settledTimezoneDate = date('Y-m-d H:i:s', strtotime($currentTimezoneDate . "-" . $toBeCompletedDays . ' days'));

        $coveredDate = $this->dateTime->date('Y-m-d H:i:s', strtotime('now -' . $toBeCompletedDays . ' days'));

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', 'shipment_processing', 'eq')
            ->addFilter('updated_at', $coveredDate, 'lteq')
            ->addFilter('store_id', $storeId, 'eq')
            ->addFilter('shipping_method', 'blackcat_homedelivery', 'eq')
            ->create();

        $orderList = $this->orderRepository->getList($searchCriteria);

        $completeOrderList = [];
        foreach ($orderList->getItems() as $order) {
            $payment = $order->getPayment();
            $eInvoiceData = json_decode($payment->getAdditionalData(), true);

            if (!empty($eInvoiceData) && $eInvoiceData["RtnCode"] == 1) {
                $completeOrderList[] = $order;
            }
        }

        return $completeOrderList;
    }

    public function OrderStatusChanger()
    {
        $stores = $this->storeManagerInterface->getStores();
        try {
            foreach ($stores as $store) {
                $isCustomOrderActive = $this->config->getChangeOrderStatusActive($store->getId());
                if ($isCustomOrderActive) {
                    $orderList = $this->getCompletedOrder($store->getId());
                    foreach ($orderList as $order) {
                        $order->setStatus('complete');
                        $order->setState('complete');
                        $this->orderRepository->save($order);
                        $this->posOrderSend($order);
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->debug("EXCEPTION OCCURRED DURING CHANGING ORDER STATUS TO COMPLETE.");
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function posOrderSend($order)
    {
        $websiteId = $order->getStore()->getWebsiteId();
        $active = $this->PointsIntegrationConfig->getActive($websiteId);
        $orderSendActive = $this->PointsIntegrationConfig->getPosOrderActive($websiteId);

        $orderData = '';
        $status = 0;
        $posSendCheck = $order->getData('pos_order_send_check');

        if ($active && $orderSendActive) {
            if (!$posSendCheck) {
                try {
                    $orderData = $this->posOrderData->getOrderData($order);

                    $response = $this->request->sendRequest($orderData, $websiteId, 'customerOrder');
                    $status = $this->responseCheck($response);

                    if ($status) {
                        $this->posOrderData->updatePosSendCheck($order->getEntityId());
                    }
                } catch (NoSuchEntityException $exception) {
                    $this->pointsIntegrationLogger->info("===== NO SUCH ENTITY EXCEPTION =====");
                    $this->pointsIntegrationLogger->info($exception->getMessage());
                    $response = $exception->getMessage();
                } catch (\Exception $exception) {
                    $this->pointsIntegrationLogger->info("===== EXCEPTION =====");
                    $this->pointsIntegrationLogger->info($exception->getMessage());
                    $response = $exception->getMessage();
                }

                $this->logging($orderData, $response, $status);
            }
        } else {
            $this->logger->info('POS ORDER REQUEST FOR ORDER : ' . $order->getIncrementId() . ' IS NOT COMPLETED DUE TO POINTS INTEGRATION MODULE INACTIVE');
        }
    }

    public function responseCheck($response)
    {
        if (isset($response['data']['statusCode']) && $response['data']['statusCode'] == '200') {
            return 1;
        } else {
            return 0;
        }
    }

    public function logging($sendData, $responseData, $status)
    {
        $this->eventManager->dispatch(
            "eguana_bizconnect_operation_processed",
            [
                'topic_name' => 'amore.pos.points-integration.order.auto',
                'direction' => 'outgoing',
                'to' => "POS",
                'serialized_data' => $this->json->serialize($sendData),
                'status' => $status,
                'result_message' => $this->json->serialize($responseData)
            ]
        );
    }

    /**
     * Get orders list having status "Shipment Processing" 24 hours ago
     *
     * @param $storeId
     * @return array
     */
    public function getShipmentProcessingOrders($storeId)
    {
        $completeOrderList = [];
        try {
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->addFieldToFilter('status', ['eq' => 'shipment_processing']);
            $orderCollection->addFieldToFilter('store_id', ['eq' => $storeId]);
            $orderCollection->getSelect()
                ->where('shipping_method IN (?)', ['blackcat_homedelivery', 'gwlogistics_CVS']);
            $query = $orderCollection->getSelect()->__toString();
            $orderList = $orderCollection->getItems();
            foreach ($orderList as $order) {
                if ($order->getShippingMethod() == 'gwlogistics_CVS') {
                    $notificationCollection = $this->statusNotificationCollection->create();
                    $notificationCollection->addFieldToFilter('order_id', ['eq' => $order->getEntityId()]);
                    $statusNotification = $notificationCollection->getFirstItem();
                    if (!empty($statusNotification) && ($statusNotification->getLogisticsSubType() === 'FAMI'
                            && $statusNotification->getRtnCode() === '3022')
                        || ($statusNotification->getLogisticsSubType() === 'UNIMART'
                            && $statusNotification->getRtnCode() === '2067')) {
                        $completeOrderList[] = $order;
                    }
                } elseif ($order->getShippingMethod() == 'blackcat_homedelivery') {
                    $updatedAt = $this->dateTime->date('Y-m-d H:i:s', strtotime($order->getUpdatedAt()));
                    $dateFrom = $this->dateTime->date('Y-m-d H:i:s', strtotime('now -7 days'));
                    if ($updatedAt <= $dateFrom) {
                        $completeOrderList[] = $order;
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->info('ERROR WHILE FETCHING ORDERS');
            $this->logger->error($exception->getMessage());
        }

        return $completeOrderList;
    }

    /**
     * Change order status to "Delivery Complete" from "Shipment Processing"
     *
     * @return void
     */
    public function changeStatusToDeliveryComplete()
    {
        $result = false;
        $stores = $this->storeManagerInterface->getStores();
        foreach ($stores as $store) {
            $isChangeOrderActive = $this->config->getChangeOrderToDeliveryCompleteActive($store->getId());
            if ($isChangeOrderActive) {
                $orderList = $this->getShipmentProcessingOrders($store->getId());
                /** @var Order $order */
                foreach ($orderList as $order) {
                    try {
                        $order->setStatus('delivery_complete');
                        $order->setState('delivery_complete');
                        $this->orderRepository->save($order);
                        $result = true;
                    } catch (\Exception $exception) {
                        $this->logger->debug('EXCEPTION OCCURRED DURING CHANGING ORDER STATUS TO "DELIVERY COMPLETE"');
                        $this->logger->debug($exception->getMessage());
                    }
                }
            }
        }
        return $result;
    }
}
