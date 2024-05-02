<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오전 11:30
 */

namespace Amore\PointsIntegration\Model;

use Amore\PointsIntegration\Model\Source\Config;
use Amore\StaffReferral\Helper\Config as ReferralConfig;
use CJ\Middleware\Helper\Data;
use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Amore\PointsIntegration\Logger\Logger;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Eguana\RedInvoice\Model\ResourceModel\RedInvoice\CollectionFactory as RedInvoiceCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;

class PosOrderData extends AbstractPosOrder
{
    const VN_LANEIGE = 'vn_laneige';

    /**
     * @var Config
     */
    private $config;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var DateTime
     */
    private $dateTime;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;


    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var Logger
     */
    private $pointsIntegrationLogger;

    /**
     * @var RedInvoiceCollectionFactory
     */
    private $redInvoiceCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $middlewareConfig;

    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    private $orderStatusHistoryRepository;

    /**
     * @var \Amasty\Rewards\Model\Config
     */
    private $amConfig;

    /**
     * @var \CJ\Rewards\Model\Data
     */
    private $rewardData;

    /**
     * @var \CJ\Middleware\Model\Data
     */
    private $orderData;

    /**
     * @param RedInvoiceCollectionFactory $redInvoiceCollectionFactory
     * @param Config $config
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param DateTime $dateTime
     * @param ResourceConnection $resourceConnection
     * @param CollectionFactory $orderCollectionFactory
     * @param Logger $pointsIntegrationLogger
     * @param StoreManagerInterface $storeManager
     * @param Data $middlewareConfig
     * @param OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository
     * @param \Amasty\Rewards\Model\Config $amConfig
     * @param ReferralConfig $referralConfig
     * @param \CJ\Rewards\Model\Data $rewardData
     * @param \CJ\Middleware\Model\Data $orderData
     */
    public function __construct(
        RedInvoiceCollectionFactory    $redInvoiceCollectionFactory,
        Config                         $config,
        SearchCriteriaBuilder          $searchCriteriaBuilder,
        OrderRepositoryInterface       $orderRepository,
        InvoiceRepositoryInterface     $invoiceRepository,
        CustomerRepositoryInterface    $customerRepository,
        DateTime                       $dateTime,
        ResourceConnection             $resourceConnection,
        CollectionFactory              $orderCollectionFactory,
        Logger                         $pointsIntegrationLogger,
        StoreManagerInterface          $storeManager,
        Data                           $middlewareConfig,
        OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository,
        \Amasty\Rewards\Model\Config $amConfig,
        ReferralConfig $referralConfig,
        \CJ\Rewards\Model\Data $rewardData,
        \CJ\Middleware\Model\Data $orderData
    ) {
        $this->redInvoiceCollectionFactory = $redInvoiceCollectionFactory;
        $this->config = $config;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->dateTime = $dateTime;
        $this->resourceConnection = $resourceConnection;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->pointsIntegrationLogger = $pointsIntegrationLogger;
        $this->storeManager = $storeManager;
        $this->middlewareConfig = $middlewareConfig;
        $this->orderStatusHistoryRepository = $orderStatusHistoryRepository;
        $this->amConfig = $amConfig;
        $this->rewardData = $rewardData;
        $this->orderData = $orderData;
        parent::__construct($referralConfig, $customerRepository, $config, $orderData);
    }

    /**
     * Get order data
     *
     * @param Order $order
     * @return array
     * @throws NoSuchEntityException
     */
    public function getOrderData($order)
    {
        $this->resetData();
        $customer = $order->getCustomerId() ? $this->getCustomer($order->getCustomerId()) : null;
        $orderItemData = $this->getItemData($order);
        $redInvoiceData = [];
        if ($order->getStore()->getCode() == self::VN_LANEIGE) {
            $redInvoice = $this->getDataRedInvoice($order->getEntityId());
            if($redInvoice->getId()) {
                $redInvoiceData = [
                    'company' => $redInvoice->getCompanyName(),
                    'taxID' => $redInvoice->getTaxCode(),
                    'address' => $redInvoice->getRoadName() . ' ' . $redInvoice->getWard() . ' ' . $redInvoice->getCity() . ' ' . $redInvoice->getState(),
                    'email' => $redInvoice->getEmail(),
                ];
            } else {
                $shippingAddress = $order->getShippingAddress();
                $redInvoiceData = [
                    'company' => $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname(),
                    'address' => preg_replace('/\r\n|\r|\n/',' ',implode(PHP_EOL, $shippingAddress->getStreet())) . ', ' .
                        $shippingAddress->getWard() . ', ' . $shippingAddress->getCity() . ', ' . $shippingAddress->getRegion(),
                    'email' => $shippingAddress->getEmail()
                ];
            }
        }

        $isDecimalFormat = $this->middlewareConfig->getIsDecimalFormat('store', $order->getStoreId());

        $orderData = $this->setOrderData($order, $orderItemData, $customer, $isDecimalFormat);
        return array_merge($orderData, $redInvoiceData);
    }

    /**
     * @param $orderId
     * @return DataObject
     */
    public function getDataRedInvoice($orderId)
    {
        $redInvoiceCollection = $this->redInvoiceCollectionFactory->create();
        $redInvoiceCollection->addFieldToFilter('order_id', $orderId);
        return $redInvoiceCollection->getFirstItem();
    }

    /**
     * Get cancel order data
     *
     * @param $order
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCancelledOrderData($order)
    {
        $this->resetData();
        $customer = $order->getCustomerId() ? $this->getCustomer($order->getCustomerId()) : null;
        $orderItemData = $this->getItemData($order);
        $isDecimalFormat = $this->middlewareConfig->getIsDecimalFormat('store', $order->getStoreId());

        return $this->setOrderData($order, $orderItemData, $customer, $isDecimalFormat, true);
    }

    /**
     * @param Order $order
     * @return array
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function getItemData(Order $order)
    {
        $orderItems = $order->getAllVisibleItems();
        $isDecimalFormat = $this->middlewareConfig->getIsDecimalFormat('store', $order->getStoreId());
        $storeId = $order->getStoreId();
        /** @var Item $orderItem */
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() != 'bundle') {
                $itemNsamt = $orderItem->getData('sap_item_nsamt');
                $itemDcamt = $orderItem->getData('sap_item_dcamt');
                $itemNetwr = $orderItem->getData('sap_item_netwr');
                $itemMiamt = $orderItem->getData('sap_item_miamt');
                $this->addOrderItemData($order, $orderItem, $itemNsamt, $itemDcamt + $itemMiamt,
                    $itemNetwr, $isDecimalFormat
                );
            } else {
                foreach ($orderItem->getChildrenItems() as $bundleChild) {
                    $itemDcamt = $bundleChild->getData('sap_item_dcamt');
                    $itemNsamt = $bundleChild->getData('sap_item_nsamt');
                    $itemSlamt = $itemNsamt - $itemDcamt;
                    $itemMiamt = $bundleChild->getData('sap_item_miamt');
                    $itemTaxAmount = $bundleChild->getData('sap_item_mwsbp');
                    $itemNetwr = $itemSlamt - $itemMiamt - $itemTaxAmount;
                    $this->addOrderItemData($order, $orderItem, $itemNsamt, $itemDcamt + $itemMiamt,
                        $itemNetwr, $isDecimalFormat, $bundleChild
                    );
                }
            }
        }

        $orderSubtotal = abs($this->orderData->roundingPrice($order->getSubtotalInclTax(), $isDecimalFormat));
        $orderGrandTotal = $order->getGrandTotal() == 0 ? $order->getGrandTotal() : $this->orderData->roundingPrice($order->getGrandTotal(), $isDecimalFormat);

        if ($this->middlewareConfig->getIsIncludeShippingAmountWhenSendRequest($storeId)) {
            $orderSubtotal += $order->getShippingAmount();
        } else {
            $orderGrandTotal -= $order->getShippingAmount();
        }

        $orderDiscountAmount = $orderSubtotal - $orderGrandTotal;

        $this->correctPricePOSOrderItemData($orderSubtotal, $orderDiscountAmount, $orderGrandTotal, $isDecimalFormat);

        if (count($orderItems) > count($this->orderItemData)) {
            throw new Exception('Missing items');
        }

        return $this->orderItemData;
    }

    /**
     * Get invoice
     *
     * @param $orderId
     * @return false|mixed|null
     */
    private function getInvoice($orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId, 'eq')
            ->create();

        $invoice = $this->invoiceRepository->getList($searchCriteria)->getItems();
        $invoiceCount = $this->invoiceRepository->getList($searchCriteria)->getTotalCount();

        if ($invoiceCount >= 1) {
            return reset($invoice);
        } else {
            return null;
        }
    }

    /**
     * Update send pos confirm flag
     *
     * @param Order $order
     */
    public function updatePosPaidOrderSendFlag(Order $order)
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $table = $connection->getTableName('sales_order');
            $detail = [
                'pos_order_paid_sent' => true,
                'pos_order_paid_send' => false
            ];
            $condition = ['entity_id = ?' => $order->getId()];
            $connection->update($table, $detail, $condition);
            $comment = $order->addCommentToStatusHistory(__('Send paid info to POS successfully'));
            $this->orderStatusHistoryRepository->save($comment);
        } catch (\Exception $exception) {
            $this->pointsIntegrationLogger->error($exception->getMessage());
        }
    }

    /**
     * @param Order $order
     */
    public function updatePosCancelledOrderSendFlag(Order $order)
    {
        try {
            $order->setData('pos_order_cancel_sent', true);
            $order->setData('pos_order_cancel_send', false);
            $comment = __('Send canceled info to POS successfully');
            $order->addCommentToStatusHistory($comment);
            $this->orderRepository->save($order);
        } catch (Exception $exception) {
            $this->pointsIntegrationLogger->error($exception->getMessage());
        }
    }

    /**
     * @param $storeId
     * @return DataObject[]
     */
    public function getPaidOrdersToPOS($storeId): array
    {
        $orderCollection = $this->orderCollectionFactory->create();

        $store = $this->storeManager->getStore($storeId);
        if ($store->getCode() == self::VN_LANEIGE) {
            $orderCollection
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('pos_order_paid_send', true)
                ->addFieldToFilter('state', Order::STATE_COMPLETE);
        } else {
            $orderCollection
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('pos_order_paid_send', true);
        }
        return $orderCollection->getItems();
    }

    /**
     * Get all order need to resend use point
     *
     * @param $storeId
     * @return array
     */
    public function getOrdersNeedToResendUsePointToPOS($storeId): array
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('pos_order_use_point_resend', true);
        return $orderCollection->getItems();
    }

    /**
     * Get all order need to resend return point
     *
     * @param $storeId
     * @return array
     */
    public function getOrdersNeedToResendReturnPointToPOS($storeId): array
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('pos_order_return_point_resend', true);
        return $orderCollection->getItems();
    }

    /**
     * Get canceled order to POS
     *
     * @param $storeId
     * @return DataObject[]
     */
    public function getCancelledOrdersToPOS($storeId): array
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('pos_order_cancel_send', true)
            ->addFieldToFilter('pos_order_paid_sent', true);

        return $orderCollection->getItems();
    }

    /**
     * Add order item data
     *
     * @param $order
     * @param $newOrderItem
     * @param $itemNsamt
     * @param $itemDcamt
     * @param $itemNetwr
     * @param $isDecimalFormat
     * @param $bundleChild
     * @return void
     */
    private function addOrderItemData(
        $order, $newOrderItem, $itemNsamt, $itemDcamt,
        $itemNetwr, $isDecimalFormat, $bundleChild = null
    ) {
        $skuPrefix = $this->getSKUPrefix($order->getStoreId()) ?: '';
        $skuPrefix = $skuPrefix ?: '';
        $sku = $newOrderItem->getSku();
        $qty = $newOrderItem->getQtyOrdered();
        if ($bundleChild) {
            $sku = $bundleChild->getSku();
            $qty = $bundleChild->getQtyOrdered();
        }

        $stripSku = str_replace($skuPrefix, '', $sku);
        $this->orderItemData[] = [
            'prdCD' => $stripSku,
            'qty' => $qty,
            'price' => $this->orderData->roundingPrice($itemNsamt/(int)$qty, $isDecimalFormat),
            'salAmt' => $this->orderData->roundingPrice($itemNsamt, $isDecimalFormat),
            'dcAmt' => $this->orderData->roundingPrice($itemDcamt, $isDecimalFormat),
            'netSalAmt' => $this->orderData->roundingPrice($itemNetwr, $isDecimalFormat)
        ];

        $this->itemsSubtotal += $itemNsamt;
        $this->itemsDiscountAmount += $itemDcamt;
        $this->itemsGrandTotal += $itemNetwr;
    }

    /**
     * Set order data
     *
     * @param $order
     * @param $orderItemData
     * @param $customer
     * @param $isDecimalFormat
     * @param $isCancelOrder
     * @return array
     */
    private function setOrderData($order, $orderItemData, $customer, $isDecimalFormat, $isCancelOrder = false)
    {
        $websiteId = $order->getStore()->getWebsiteId();
        $posIntegrationNumber = $customer && $customer->getCustomAttribute('integration_number') ?
            $customer->getCustomAttribute('integration_number')->getValue() : null;
        $couponCode = $order->getCouponCode();
        $invoice = $this->getInvoice($order->getEntityId());
        $baReferralCode = $this->getReferralBACode($order, $websiteId);
        $friendReferralCode = $this->getFriendReferralCode($order);

        if ($isCancelOrder) {
            $saleDate = $this->orderData->dateFormatting($this->dateTime->gmtDate(), 'Ymd');
            $orderId ='C' . $order->getIncrementId();
            $orderType = self::POS_ORDER_TYPE_CANCEL;
        } else {
            $saleDate = $this->orderData->dateFormatting($order->getCreatedAt(), 'Ymd');
            $orderId = $order->getIncrementId();
            $orderType = self::POS_ORDER_TYPE_ORDER;
            if ($order->getStore()->getCode() == self::VN_LANEIGE) {
                $saleDate = $this->orderData->dateFormatting($this->dateTime->gmtDate(), 'Ymd');
            }
            $redemptionFlag = 'N';
            $rewardPoints = 0;
            $storeId = $order->getStoreId();
            if($this->amConfig->isEnabled($storeId)) {
                if ($order->getData('am_spent_reward_points')) {
                    $rewardPoints = $this->orderData->roundingPrice($order->getData('am_spent_reward_points'), $isDecimalFormat);
                }
                $spendingRate = $this->amConfig->getPointsRate($storeId);
                if (!$spendingRate) {
                    $spendingRate = 1;
                }
                if ($this->rewardData->isEnableShowListOptionRewardPoint($storeId)) {
                    $listOptions = $this->rewardData->getListOptionRewardPoint($storeId);
                    $discountFromPoints = $listOptions[$rewardPoints] ?? 0;
                } else {
                    $discountFromPoints = $rewardPoints / $spendingRate;
                }

                if (($order->getGrandTotal() - $order->getShippingAmount()) == $discountFromPoints) {
                    $redemptionFlag = 'Y';
                }
            }
        }

        $orderData = [
            'salOrgCd' => $this->middlewareConfig->getSalesOrganizationCode('store', $websiteId),
            'salOffCd' => $this->middlewareConfig->getSalesOfficeCode('store', $websiteId),
            'saledate' => $saleDate,
            'orderID' => $orderId,
            'rcptNO' => 'I' . $invoice->getIncrementId(),
            'cstmIntgSeq' => $posIntegrationNumber,
            'orderType' => $orderType,
            'promotionKey' => $couponCode,
            'orderInfo' => $orderItemData,
            'baReferralCode' => $baReferralCode,
            'ffReferralCode' => $friendReferralCode
        ];

        if (!$isCancelOrder) {
            $orderData['redemptionFlag'] = $redemptionFlag;
            $orderData['PointAccount'] = (int)$rewardPoints;
        }
        return $orderData;
    }
}
