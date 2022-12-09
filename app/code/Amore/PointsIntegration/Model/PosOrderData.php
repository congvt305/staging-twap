<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오전 11:30
 */

namespace Amore\PointsIntegration\Model;

use Amore\PointsIntegration\Model\Source\Config;
use CJ\Middleware\Helper\Data;
use CJ\PointRedemption\Setup\Patch\Data\AddRedemptionAttributes;
use Exception;
use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Amore\PointsIntegration\Logger\Logger;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Eguana\RedInvoice\Model\ResourceModel\RedInvoice\CollectionFactory as RedInvoiceCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;

class PosOrderData
{
    const VN_LANEIGE = 'vn_laneige';
    const POS_ORDER_TYPE_ORDER = '000010';
    const POS_ORDER_TYPE_CANCEL = '000030';
    const SKU_PREFIX_XML_PATH = 'sap/mall_info/sku_prefix';

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
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var ProductLinkManagementInterface
     */
    private $productLinkManagement;
    /**
     * @var OrderItemRepositoryInterface
     */
    protected $orderItemRepository;
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
     * @param RedInvoiceCollectionFactory $redInvoiceCollectionFactory
     * @param Config $config
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ProductLinkManagementInterface $productLinkManagement
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param DateTime $dateTime
     * @param ResourceConnection $resourceConnection
     * @param CollectionFactory $orderCollectionFactory
     * @param Logger $pointsIntegrationLogger
     * @param StoreManagerInterface $storeManager
     * @param Data $middlewareConfig
     * @param OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository
     */
    public function __construct(
        RedInvoiceCollectionFactory    $redInvoiceCollectionFactory,
        Config                         $config,
        SearchCriteriaBuilder          $searchCriteriaBuilder,
        OrderRepositoryInterface       $orderRepository,
        InvoiceRepositoryInterface     $invoiceRepository,
        CustomerRepositoryInterface    $customerRepository,
        ProductRepositoryInterface     $productRepository,
        ProductLinkManagementInterface $productLinkManagement,
        OrderItemRepositoryInterface   $orderItemRepository,
        DateTime                       $dateTime,
        ResourceConnection             $resourceConnection,
        CollectionFactory              $orderCollectionFactory,
        Logger                         $pointsIntegrationLogger,
        StoreManagerInterface          $storeManager,
        Data                           $middlewareConfig,
        OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository
    )
    {
        $this->redInvoiceCollectionFactory = $redInvoiceCollectionFactory;
        $this->config = $config;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->productLinkManagement = $productLinkManagement;
        $this->orderItemRepository = $orderItemRepository;
        $this->dateTime = $dateTime;
        $this->resourceConnection = $resourceConnection;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->pointsIntegrationLogger = $pointsIntegrationLogger;
        $this->storeManager = $storeManager;
        $this->middlewareConfig = $middlewareConfig;
        $this->orderStatusHistoryRepository = $orderStatusHistoryRepository;
    }

    /**
     * @param Order $order
     * @return array
     * @throws NoSuchEntityException
     */
    public function getOrderData($order)
    {
        $customer = $order->getCustomerId() ? $this->getCustomer($order->getCustomerId()) : null;
        $websiteId = $order->getStore()->getWebsiteId();
        $posIntegrationNumber = $customer && $customer->getCustomAttribute('integration_number') ?
            $customer->getCustomAttribute('integration_number')->getValue() : null;

        $orderItemData = $this->getItemData($order);
        $couponCode = $order->getCouponCode();
        $invoice = $this->getInvoice($order->getEntityId());
        $saleDate = $this->dateFormat($order->getCreatedAt());
        $redInvoiceData = [];
        if ($order->getStore()->getCode() == self::VN_LANEIGE) {
            $saleDate = $this->dateFormat('now');
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
        $orderData = [
            'salOrgCd' => $this->config->getOrganizationSalesCode($websiteId),
            'salOffCd' => $this->config->getOfficeSalesCode($websiteId),
            'saledate' => $saleDate,
            'orderID' => $order->getIncrementId(),
            'rcptNO' => 'I' . $invoice->getIncrementId(),
            'cstmIntgSeq' => $posIntegrationNumber,
            'orderType' => self::POS_ORDER_TYPE_ORDER,
            'promotionKey' => $couponCode,
            'orderInfo' => $orderItemData
        ];
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
     * @param $order
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCancelledOrderData($order)
    {
        $customer = $order->getCustomerId() ? $this->getCustomer($order->getCustomerId()) : null;
        $websiteId = $order->getStore()->getWebsiteId();
        $posIntegrationNumber = $customer && $customer->getCustomAttribute('integration_number') ?
            $customer->getCustomAttribute('integration_number')->getValue() : null;

        $orderItemData = $this->getItemData($order);
        $couponCode = $order->getCouponCode();
        $invoice = $this->getInvoice($order->getEntityId());

        return [
            'salOrgCd' => $this->config->getOrganizationSalesCode($websiteId),
            'salOffCd' => $this->config->getOfficeSalesCode($websiteId),
            'saledate' => $this->dateFormat('now'),
            'orderID' => 'C' . $order->getIncrementId(),
            'rcptNO' => 'I' . $invoice->getIncrementId(),
            'cstmIntgSeq' => $posIntegrationNumber,
            'orderType' => self::POS_ORDER_TYPE_CANCEL,
            'promotionKey' => $couponCode,
            'orderInfo' => $orderItemData
        ];
    }


    public function dateFormat($date)
    {
        return date("Ymd", strtotime($date));
    }

    public function validateCoupon($couponCode)
    {
        if (strpos($couponCode, 'TW') !== false) {
            return $couponCode;
        } else {
            return '';
        }
    }

    /**
     * @param Order $order
     * @return array
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function getItemData(Order $order)
    {
        $orderItemData = [];
        $itemsSubtotal = 0;
        $itemsDiscountAmount = 0;
        $itemsGrandTotal = 0;
        $itemsPointTotal = 0;
        $skuPrefix = $this->getSKUPrefix($order->getStoreId()) ?: '';
        $orderItems = $order->getAllVisibleItems();
        $isDecimalFormat = $this->middlewareConfig->getIsDecimalFormat('store', $order->getStoreId());

        /** @var Item $orderItem */
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() != 'bundle') {
                $itemSubtotal = $this->simpleAndConfigurableSubtotal($orderItem, $isDecimalFormat);
                $itemTotalDiscount = $this->simpleAndConfigurableTotalDiscount($orderItem, $isDecimalFormat);
                $itemGrandTotal = $itemSubtotal - $itemTotalDiscount;
                $stripSku = str_replace($skuPrefix, '', $orderItem->getSku());
                $isRedemptionItem = $orderItem->getData('is_point_redeemable');
                $pointAccount = $orderItem->getQtyOrdered() * $orderItem->getData('point_redemption_amount');
                $orderItemData[] = [
                    'prdCD' => $stripSku,
                    'qty' => (int)$orderItem->getQtyOrdered(),
                    'price' => $this->roundingPrice($orderItem->getOriginalPrice(), $isDecimalFormat),
                    'salAmt' => $this->roundingPrice($itemSubtotal, $isDecimalFormat),
                    'dcAmt' => $this->roundingPrice($itemTotalDiscount, $isDecimalFormat),
                    'netSalAmt' => $this->roundingPrice($itemGrandTotal, $isDecimalFormat),
                    'redemptionFlag' => $isRedemptionItem ? 'Y' : 'N',
                    'pointAccount' => (int)$pointAccount
                ];

                $itemsSubtotal += $itemSubtotal;
                $itemsDiscountAmount += $itemTotalDiscount;
                $itemsGrandTotal += $itemGrandTotal;
                $itemsPointTotal += $pointAccount;
            } else {
                /** @var Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId());
                $bundleChildren = $orderItem->getChildrenItems();
                $bundlePriceType = $bundleProduct->getPriceType();
                $isRedemptionItem = $orderItem->getData('is_point_redeemable');
                $totalPointAmount = $orderItem->getData('point_redemption_amount') * $orderItem->getQtyOrdered();

                foreach ($bundleChildren as $bundleChild) {
                    $stripSku = str_replace($skuPrefix, '', $bundleChild->getSku());
                    if ((int)$bundlePriceType !== Price::PRICE_TYPE_DYNAMIC) {
                        $bundleChildPrice = $this->productRepository->get($bundleChild->getSku(), false, $order->getStoreId())->getPrice();
                    } else {
                        $bundleChildPrice = $bundleChild->getOriginalPrice();
                    }

                    $product = $this->productRepository->get($bundleChild->getSku(), false, $order->getStoreId());
                    $bundleChildSubtotal = $this->bundleChildSubtotal($bundleChildPrice, $bundleChild, $isDecimalFormat);
                    $bundleChildDiscountAmount = $this->getBundleChildDiscountAmount($bundlePriceType, $orderItem, $bundleChild, $isDecimalFormat);
                    $priceGap = $orderItem->getOriginalPrice() - $orderItem->getPrice();

                    $qtyPerBundle = $bundleChild->getQtyOrdered() / $orderItem->getQtyOrdered();
                    $childPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getOriginalPrice()) / $qtyPerBundle;
                    $catalogRuledPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, ($priceGap)) / $qtyPerBundle;
                    $itemTotalDiscount = abs($this->roundingPrice(
                        $bundleChildDiscountAmount +
                        (($product->getPrice() - $childPriceRatio) * $bundleChild->getQtyOrdered()) +
                        $catalogRuledPriceRatio * $bundleChild->getQtyOrdered(),
                        $isDecimalFormat
                    ));
                    $bundleChildGrandTotal = $bundleChildSubtotal - $itemTotalDiscount;
                    $pointAccount = 0;
                    if ($isRedemptionItem) {
                        $pointAccount = $totalPointAmount / count($bundleChildren);
                    }

                    $orderItemData[] = [
                        'prdCD' => $stripSku,
                        'qty' => (int)$bundleChild->getQtyOrdered(),
                        'price' => $this->roundingPrice($bundleChildPrice, $isDecimalFormat),
                        'salAmt' => $this->roundingPrice($bundleChildSubtotal, $isDecimalFormat),
                        'dcAmt' => $this->roundingPrice($itemTotalDiscount, $isDecimalFormat),
                        'netSalAmt' => $this->roundingPrice($bundleChildGrandTotal, $isDecimalFormat),
                        'redemptionFlag' => $isRedemptionItem ? 'Y' : 'N',
                        'pointAccount' => (int)$pointAccount
                    ];

                    $itemsSubtotal += $bundleChildSubtotal;
                    $itemsDiscountAmount += $itemTotalDiscount;
                    $itemsGrandTotal += $bundleChildGrandTotal;
                    $itemsPointTotal += (int)$pointAccount;
                }
            }
        }

        $orderSubtotal = $this->getOrderSubtotal($order, $isDecimalFormat);
        $orderGrandTotal = $this->getOrderGrandTotal($order, $isDecimalFormat);
        $orderDiscount = $orderSubtotal - $orderGrandTotal;
        $orderPointTotal = $this->getOrderPointTotal($order);

        $orderItemData = $this->priceCorrector($orderSubtotal, $itemsSubtotal, $orderItemData, 'salAmt', $isDecimalFormat);
        $orderItemData = $this->priceCorrector($orderDiscount, $itemsDiscountAmount, $orderItemData, 'dcAmt', $isDecimalFormat);
        $orderItemData = $this->priceCorrector($orderGrandTotal, $itemsGrandTotal, $orderItemData, 'netSalAmt', $isDecimalFormat);
        $orderItemData = $this->priceCorrector($orderPointTotal, $itemsPointTotal, $orderItemData, 'pointAccount', $isDecimalFormat);

        if (count($orderItems) > count($orderItemData)) {
            throw new Exception('Missing items');
        }

        if ($isDecimalFormat) {
            $listToFormat = ['salAmt', 'dcAmt', 'netSalAmt', 'pointAccount', 'price'];

            foreach ($listToFormat as $field) {
                foreach ($orderItemData as $key => $value) {
                    if (isset($value[$field]) && (is_float($value[$field]) || is_int($value[$field]))) {
                        $orderItemData[$key][$field] = $this->formatPrice($value[$field], $isDecimalFormat);
                    }
                }
            }
        }

        return $orderItemData;
    }

    /**
     * @param $orderAmount
     * @param $itemTotalAmount
     * @param $orderItemData
     * @param $field
     * @param $isDecimalFormat
     * @return array
     */
    public function priceCorrector($orderAmount, $itemTotalAmount, $orderItemData, $field, $isDecimalFormat = false)
    {
        if ($orderAmount != $itemTotalAmount) {
            $amountDifference = $orderAmount - $itemTotalAmount;

            foreach ($orderItemData as $key => $value) {
                if ($value['price'] == 0) {
                    continue;
                }
                $orderItemData[$key][$field] = $this->formatPrice($value[$field] + $amountDifference, $isDecimalFormat);
                break;
            }
        }

        return $orderItemData;
    }

    /**
     * @param Order $order
     * @return float|int
     */
    public function getOrderPointTotal(Order $order)
    {
        $pointTotal = 0;
        $orderItems = $order->getAllVisibleItems();
        foreach ($orderItems as $item) {
            if ($item->getData(AddRedemptionAttributes::IS_POINT_REDEEMABLE_ATTRIBUTE_CODE)) {
                $pointTotal += $item->getData(AddRedemptionAttributes::POINT_REDEMPTION_AMOUNT_ATTRIBUTE_CODE)
                    * $item->getQtyOrdered();
            }
        }
        return $pointTotal;
    }

    /**
     * @param $order
     * @param $isDecimalFormat
     * @return float
     * @throws NoSuchEntityException
     */
    public function getOrderSubtotal($order, $isDecimalFormat = false)
    {
        return $this->roundingPrice($order->getSubtotal() + $this->getBundleExtraAmount($order) + $this->getCatalogRuleDiscountAmount($order), $isDecimalFormat);
    }

    /**
     * @param Order $order
     * @return float|null
     */
    public function getOrderGrandTotal(Order $order, $isDecimalFormat = false)
    {
        return $order->getGrandTotal() == 0 ? $order->getGrandTotal() : $this->roundingPrice($order->getGrandTotal() - $order->getShippingAmount(), $isDecimalFormat);
    }

    /**
     * @param $order Order
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function getBundleExtraAmount($order)
    {
        $orderItems = $order->getAllVisibleItems();
        $priceDifferences = 0;

        /** @var Item $orderItem */
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() == 'bundle') {
                /** @var Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId(), false, $order->getStoreId());
                $bundlePriceType = $bundleProduct->getPriceType();

                if ((int)$bundlePriceType !== Price::PRICE_TYPE_DYNAMIC) {
                    foreach ($orderItem->getChildrenItems() as $bundleChild) {
                        $qtyPerBundle = $bundleChild->getQtyOrdered() / $orderItem->getQtyOrdered();
                        $childPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getOriginalPrice()) / $qtyPerBundle;
                        $originItemPrice = $this->productRepository->get($bundleChild->getSku(), false, $order->getStoreId())->getPrice();

                        $priceDifferences += (($originItemPrice - $childPriceRatio) * $bundleChild->getQtyOrdered());
                    }
                }
            }
        }
        return $priceDifferences;
    }

    /**
     * @param $order Order
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function getCatalogRuleDiscountAmount($order)
    {
        $catalogRuleDiscount = 0;
        $orderItems = $order->getAllVisibleItems();
        /** @var Item $orderItem */
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() != 'bundle') {
                $catalogRuleDiscount += ($orderItem->getOriginalPrice() - $orderItem->getPrice()) * $orderItem->getQtyOrdered();
            } else {
                /** @var Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId(), false, $order->getStoreId());
                $bundlePriceType = $bundleProduct->getPriceType();

                if ((int)$bundlePriceType !== Price::PRICE_TYPE_DYNAMIC) {
                    foreach ($orderItem->getChildrenItems() as $bundleChild) {
                        $qtyPerbundle = $bundleChild->getQtyOrdered() / $orderItem->getQtyOrdered();
                        $catalogRuledPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, ($orderItem->getOriginalPrice() - $orderItem->getPrice())) / $qtyPerbundle;

                        $catalogRuleDiscount += $catalogRuledPriceRatio * $bundleChild->getQtyOrdered();
                    }
                }
            }
        }
        return $catalogRuleDiscount;
    }

    /**
     * @param Item $orderItem
     * @param $isDecimalFormat
     * @return float|int
     */
    public function simpleAndConfigurableSubtotal(Item $orderItem, $isDecimalFormat = false)
    {
        return abs($this->roundingPrice($orderItem->getOriginalPrice() * $orderItem->getQtyOrdered(), $isDecimalFormat));
    }

    /**
     * @param Item $orderItem
     * @param $isDecimalFormat
     * @return float|int
     */
    public function simpleAndConfigurableTotalDiscount(Item $orderItem, $isDecimalFormat = false)
    {
        return abs($this->roundingPrice($orderItem->getDiscountAmount() +
            (($orderItem->getOriginalPrice() - $orderItem->getPrice()) * $orderItem->getQtyOrdered()),
            $isDecimalFormat));
    }

    /**
     * @param $bundleChildPrice
     * @param $bundleChildFromOrder
     * @param $isDecimalFormat
     * @return float|int
     */
    public function bundleChildSubtotal($bundleChildPrice, $bundleChildFromOrder, $isDecimalFormat)
    {
        return abs($this->roundingPrice($bundleChildPrice * $bundleChildFromOrder->getQtyOrdered(), $isDecimalFormat));
    }

    /**
     * Get new bundle child discount amount
     * @param $bundlePriceType
     * @param $orderItem
     * @param $bundleChild
     * @param $isDecimalFormat
     * @return float
     * @throws NoSuchEntityException
     *
     */
    public function getBundleChildDiscountAmount($bundlePriceType, $orderItem, $bundleChild, $isDecimalFormat = false)
    {
        $bundleChildDiscountAmount = (int)$bundlePriceType !== Price::PRICE_TYPE_DYNAMIC ?
            $this->roundingPrice($this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getDiscountAmount()), $isDecimalFormat) :
            $this->roundingPrice($bundleChild->getDiscountAmount(), $isDecimalFormat);

        return $bundleChildDiscountAmount;
    }
    /**
     * @param $orderId
     * @return OrderInterface
     */
    public function getOrder($orderId)
    {
        return $this->orderRepository->get($orderId);
    }

    /**
     * Get proportion of bundle child
     *
     * @param Item $orderItem
     * @param Item $bundleChild
     * @param float $valueToCalculate
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getProportionOfBundleChild($orderItem, $bundleChild, $valueToCalculate)
    {
        $bundleChildrenTotalAmount = $this->getSumOfChildrenOriginPrice($orderItem);

        $bundleChildPrice = $this->productRepository->get($bundleChild->getSku(), false, $orderItem->getStoreId())->getPrice();
        $rate = ($bundleChildPrice / $bundleChildrenTotalAmount) * ($bundleChild->getQtyOrdered() / $orderItem->getQtyOrdered());

        return $valueToCalculate * $rate;
    }

    /**
     * Get  bundle children total amount
     *
     * @param Item $orderItem
     * @return float|null
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function getSumOfChildrenOriginPrice(Item $orderItem)
    {
        $originalPriceTotal = 0;

        foreach ($orderItem->getChildrenItems() as $childItem) {
            $originalProductPrice = $this->productRepository->get($childItem->getSku(), false, $orderItem->getStoreId())->getPrice();
            //get total price for product per bundle
            $originalPriceTotal += ($originalProductPrice * ($childItem->getQtyOrdered() / $orderItem->getQtyOrdered()));
        }
        return $originalPriceTotal;
    }

    public function getInvoice($orderId)
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
            $this->pointsIntegrationLogger->err($exception->getMessage());
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
            $this->pointsIntegrationLogger->err($exception->getMessage());
        }
    }

    public function getCustomer($customerId)
    {
        return $this->customerRepository->getById($customerId);
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
     * @param $storeId
     * @return mixed
     */
    public function getSKUPrefix($storeId)
    {
        return $this->config->getValue(
            self::SKU_PREFIX_XML_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getPointAccount($bundleItem)
    {
        $childrenItems = $bundleItem->getChildrenItems();
        $totalQty = 0;
        /** @var Item $item */
        foreach ($childrenItems as $item) {
            $totalQty = $totalQty + $item->getQtyOrdered();
        }
        $totalPointAmount = $bundleItem->getData('point_redemption_amount') * $bundleItem->getQtyOrdered();
        return (int)($totalPointAmount  / $totalQty);
    }

    /**
     * @param $price
     * @param false $isDecimal
     * @return int|string
     */
    public function formatPrice($price, $isDecimal = false)
    {
        if ($isDecimal) {
            return number_format($price, 2, '.', '');
        }
        return (int)$price;
    }

    /**
     * @param $price
     * @param $isDecimal
     * @return float
     */
    public function roundingPrice($price, $isDecimal = false)
    {
        $precision = $isDecimal ? 2 : 0;
        return round($price, $precision);
    }
}
