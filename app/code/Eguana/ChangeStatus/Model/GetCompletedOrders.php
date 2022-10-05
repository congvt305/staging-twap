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
     * @var \Magento\Framework\Filesystem\Io\Sftp
     */
    private $sftp;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
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
     * @param \Magento\Framework\Filesystem\Io\Sftp $sftp
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
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
        NotificationCollectionFactory $statusNotificationCollection,
        \Magento\Framework\Filesystem\Io\Sftp $sftp,
        \Magento\Sales\Model\OrderFactory $orderFactory
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
        $this->sftp = $sftp;
        $this->orderFactory = $orderFactory;
    }

    public function getCompletedOrder($storeId)
    {
        $toBeCompletedDays = is_null($this->config->getAvailableReturnDays($storeId)) ? 7 : $this->config->getAvailableReturnDays($storeId);
        $gmtDate = $this->dateTime->gmtDate();
        $timezone = $this->timezone->getConfigTimezone('store', $storeId);
        $storeTime = $this->timezone->formatDateTime($gmtDate, 3, 3, null, $timezone);
        $isCronChangeOrderStatusForBlackCat = $this->config->getChangeOrderToDeliveryCompleteForTWBlackCatActive();
        $store = $this->storeManagerInterface->getStore($storeId);
        $currentTimezoneDate = date('Y-m-d H:i:s', strtotime($storeTime));
        $settledTimezoneDate = date('Y-m-d H:i:s', strtotime($currentTimezoneDate . "-" . $toBeCompletedDays . ' days'));

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
            $eInvoiceData = json_decode($payment->getAdditionalData(), true);

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
                $isCustomOrderActive = $this->config->getChangeOrderStatusActive($store->getId());
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
            $isCronChangeOrderStatusForBlackCat = $this->config->getChangeOrderToDeliveryCompleteForTWBlackCatActive();
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
            $updateAfterDays = (int)$this->config->getDaysUpdateNinjaVanOrderToDeliveryComplete($storeId);
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
                    'host' => $this->config->getChangeOrderToDeliveryCompleteForBlackCatUrl(),
                    'username' => $this->config->getChangeOrderToDeliveryCompleteForBlackCatUsername(),
                    'password' => $this->config->getChangeOrderToDeliveryCompleteForBlackCatPassword(),
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
        $isChangeOrderActive = $this->config->getChangeOrderToDeliveryCompleteForTWBlackCatActive();
        $host = $this->config->getChangeOrderToDeliveryCompleteForBlackCatUrl();
        $username = $this->config->getChangeOrderToDeliveryCompleteForBlackCatUsername();
        $password =  $this->config->getChangeOrderToDeliveryCompleteForBlackCatPassword();
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
