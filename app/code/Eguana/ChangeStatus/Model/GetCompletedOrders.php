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

use CJ\Middleware\Helper\Data as MiddlewareHelper;
use Eguana\GWLogistics\Model\ResourceModel\StatusNotification\CollectionFactory as NotificationCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use CJ\Middleware\Model\PosRequest;
use Eguana\ChangeStatus\Model\Source\Config as ChangeStatusConfig;

class GetCompletedOrders extends PosRequest
{
    const BLACK_CAT_ORDER_ARRIVED_STATUS = '00003';

    const TW_STORE_CODE = [
        'default',
        'tw_laneige'
    ];

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
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var \Amore\PointsIntegration\Model\PosOrderData
     */
    private $posOrderData;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Amore\PointsIntegration\Model\Source\Config
     */
    private $PointsIntegrationConfig;


    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var NotificationCollectionFactory
     */
    private $statusNotificationCollection;

    /**
     * @var \Magento\Framework\Filesystem\Io\Sftp
     */
    private $sftp;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var ChangeStatusConfig
     */
    private $changeStatusConfig;

    /**
     * @param Curl $curl
     * @param MiddlewareHelper $middlewareHelper
     * @param LoggerInterface $logger
     * @param \Amore\PointsIntegration\Model\Source\Config $config
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param DateTime $dateTime
     * @param StoreManagerInterface $storeManagerInterface
     * @param TimezoneInterface $timezone
     * @param \Amore\PointsIntegration\Model\PosOrderData $posOrderData
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Amore\PointsIntegration\Model\Source\Config $PointsIntegrationConfig
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param NotificationCollectionFactory $statusNotificationCollection
     * @param \Magento\Framework\Filesystem\Io\Sftp $sftp
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param ChangeStatusConfig $changeStatusConfig
     */
    public function __construct(
        Curl $curl,
        MiddlewareHelper $middlewareHelper,
        LoggerInterface $logger,
        \Amore\PointsIntegration\Model\Source\Config $config,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        DateTime $dateTime,
        StoreManagerInterface $storeManagerInterface,
        TimezoneInterface $timezone,
        \Amore\PointsIntegration\Model\PosOrderData $posOrderData,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Amore\PointsIntegration\Model\Source\Config $PointsIntegrationConfig,
        OrderCollectionFactory $orderCollectionFactory,
        NotificationCollectionFactory $statusNotificationCollection,
        \Magento\Framework\Filesystem\Io\Sftp $sftp,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        ChangeStatusConfig $changeStatusConfig
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->dateTime = $dateTime;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->timezone = $timezone;
        $this->posOrderData = $posOrderData;
        $this->eventManager = $eventManager;
        $this->PointsIntegrationConfig = $PointsIntegrationConfig;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->statusNotificationCollection = $statusNotificationCollection;
        $this->sftp = $sftp;
        $this->orderFactory = $orderFactory;
        $this->changeStatusConfig = $changeStatusConfig;
        parent::__construct($curl, $middlewareHelper, $logger, $config);
    }

    public function getCompletedOrder($storeId)
    {
        $toBeCompletedDays = is_null($this->changeStatusConfig->getAvailableReturnDays($storeId)) ? 7 : $this->changeStatusConfig->getAvailableReturnDays($storeId);
        $gmtDate = $this->dateTime->gmtDate();
        $timezone = $this->timezone->getConfigTimezone('store', $storeId);
        $storeTime = $this->timezone->formatDateTime($gmtDate, 3, 3, null, $timezone);
        $isCronChangeOrderStatusForBlackCat = $this->changeStatusConfig->getChangeOrderToDeliveryCompleteForTWBlackCatActive();
        $store = $this->storeManagerInterface->getStore($storeId);
        $currentTimezoneDate = date('Y-m-d H:i:s', strtotime($storeTime));

        $coveredDate = $this->dateTime->date('Y-m-d H:i:s', strtotime('now -' . $toBeCompletedDays . ' days'));

        $this->searchCriteriaBuilder
            ->addFilter('status', 'shipment_processing', 'eq')
            ->addFilter('updated_at', $coveredDate, 'lteq')
            ->addFilter('store_id', $storeId, 'eq');
        if ($isCronChangeOrderStatusForBlackCat && in_array($store->getCode(), self::TW_STORE_CODE)) {
            $this->searchCriteriaBuilder->addFilter('shipping_method', ['eguanadhl_tablerate', 'ninjavan_tablerate'], 'in');
        } else {
            $this->searchCriteriaBuilder->addFilter('shipping_method', ['blackcat_homedelivery', 'eguanadhl_tablerate', 'ninjavan_tablerate'], 'in');

        }
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $orderList = $this->orderRepository->getList($searchCriteria);

        $completeOrderList = [];
        foreach ($orderList->getItems() as $order) {
            $payment = $order->getPayment();
            $eInvoiceData = null;
            if ($payment->getAdditionalData()) {
                $eInvoiceData = $this->middlewareHelper->serializeData($payment->getAdditionalData());
            }

            if (in_array($payment->getMethod(), ['ecpay_ecpaypayment', 'linepay_payment'])) {
                if (!empty($eInvoiceData) && $eInvoiceData["RtnCode"] == 1) {
                    $completeOrderList[] = $order;
                }
            } else {
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
                $isCustomOrderActive = $this->changeStatusConfig->getChangeOrderStatusActive($store->getId());
                if ($isCustomOrderActive) {
                    $orderList = $this->getCompletedOrder($store->getId());
                    foreach ($orderList as $order) {
                        $order->setStatus('complete');
                        $order->setState('complete');
                        $this->orderRepository->save($order);
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->debug("EXCEPTION OCCURRED DURING CHANGING ORDER STATUS TO COMPLETE.");
            $this->logger->debug($exception->getMessage());
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
                'serialized_data' => $this->middlewareHelper->serializeData($sendData),
                'status' => $status,
                'result_message' => $this->middlewareHelper->serializeData($responseData)
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
            $isCronChangeOrderStatusForBlackCat = $this->changeStatusConfig->getChangeOrderToDeliveryCompleteForTWBlackCatActive();
            $store = $this->storeManagerInterface->getStore($storeId);
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->addFieldToFilter('status', ['eq' => 'shipment_processing']);
            $orderCollection->addFieldToFilter('store_id', ['eq' => $storeId]);
            if ($isCronChangeOrderStatusForBlackCat && in_array($store->getCode(), self::TW_STORE_CODE)) {
                $orderCollection->getSelect()
                    ->where('shipping_method IN (?)', ['gwlogistics_CVS', 'eguanadhl_tablerate', 'ninjavan_tablerate']);
            } else {
                $orderCollection->getSelect()
                    ->where('shipping_method IN (?)', ['blackcat_homedelivery', 'gwlogistics_CVS', 'eguanadhl_tablerate', 'ninjavan_tablerate']);
            }

            $orderList       = $orderCollection->getItems();
            $updateAfterDays = (int)$this->changeStatusConfig->getDaysUpdateNinjaVanOrderToDeliveryComplete($storeId);
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
                } elseif ($order->getShippingMethod() == 'blackcat_homedelivery' || $order->getShippingMethod() == 'eguanadhl_tablerate') {
                    $updatedAt = $this->dateTime->date('Y-m-d H:i:s', strtotime($order->getUpdatedAt()));
                    $dateFrom = $this->dateTime->date('Y-m-d H:i:s', strtotime('now -7 days'));
                    if ($updatedAt <= $dateFrom) {
                        $completeOrderList[] = $order;
                    }
                } elseif ($order->getShippingMethod() == 'ninjavan_tablerate') {
                    $updatedAt = $this->dateTime->date('Y-m-d H:i:s', strtotime($order->getUpdatedAt()));
                    $dateFrom  = $this->dateTime->date('Y-m-d H:i:s', strtotime('now -'. $updateAfterDays .' days'));
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
            $isChangeOrderActive = $this->changeStatusConfig->getChangeOrderToDeliveryCompleteActive($store->getId());
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


    /**
     * Change order status to "Delivery Complete" from "Shipment Processing" for black cat by call API
     *
     * @return array
     */
    public function getOrderNeedToChangeStatusToDeliveryCompleteForBlackCat()
    {
        $orderNeedToCheck = [];
        $dataToUpdate = [];
        try {
            $this->sftp->open(
                [
                    'host' => $this->changeStatusConfig->getChangeOrderToDeliveryCompleteForBlackCatUrl(),
                    'username' => $this->changeStatusConfig->getChangeOrderToDeliveryCompleteForBlackCatUsername(),
                    'password' => $this->changeStatusConfig->getChangeOrderToDeliveryCompleteForBlackCatPassword(),
                ]
            );
        } catch (\Exception $e) {
            $this->logger->debug("ERROR WHEN CONNECT TO SERVER SFTP FOR BLACK CAT : " . $e->getMessage());
        }

        $gmtDate = $this->dateTime->gmtDate();
        //because it just use for TW so get TW SWS or TW LNG is the same
        $storeId = $this->storeManagerInterface->getStore('default')->getId();
        $timezone = $this->timezone->getConfigTimezone('store', $storeId);
        $storeTime = $this->timezone->formatDateTime($gmtDate, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT, null, $timezone);
        $content = $this->sftp->read('focus' . date('mdH', strtotime($storeTime. ' -1 hours')) . '.SOD');
        if ($content) {
            $dataEachLine = explode(PHP_EOL, $content);
            foreach ($dataEachLine as $eachLineArr) {
                $dataToUpdate[] = explode('|', $eachLineArr);
            }
            foreach ($dataToUpdate as $data) {
                if (isset($data[5]) && $data[5] == self::BLACK_CAT_ORDER_ARRIVED_STATUS) {
                    $orderNeedToCheck[] = $data;
                }
            }
        }
        return $orderNeedToCheck;
    }

    /**
     * Change order status to delivery for black cat
     *
     * @return bool
     */
    public function changeStatusToDeliveryCompleteForBlackCat()
    {
        $isChangeOrderActive = $this->changeStatusConfig->getChangeOrderToDeliveryCompleteForTWBlackCatActive();
        $host = $this->changeStatusConfig->getChangeOrderToDeliveryCompleteForBlackCatUrl();
        $username = $this->changeStatusConfig->getChangeOrderToDeliveryCompleteForBlackCatUsername();
        $password =  $this->changeStatusConfig->getChangeOrderToDeliveryCompleteForBlackCatPassword();
        if ($isChangeOrderActive && $host && $username && $password) {
            $orderNeedToCheck = $this->getOrderNeedToChangeStatusToDeliveryCompleteForBlackCat();
            foreach ($orderNeedToCheck as $orderData) {
                try {
                    $orderModel = $this->orderFactory->create();
                    $order = $orderModel->loadByIncrementId($orderData[1]);
                    if ($order->getStatus() == 'shipment_processing') {
                        $order->setStatus('delivery_complete');
                        $order->setState('delivery_complete');
                        $this->orderRepository->save($order);
                    }
                } catch (\Exception $e) {
                    $this->logger->debug('ERROR WHEN UPDATED STATUS FOR TW ORDER: ' . $orderData[1] . ' ERROR: ' . $e->getMessage());
                    continue;
                }
            }
            return true;
        }
    }
}
