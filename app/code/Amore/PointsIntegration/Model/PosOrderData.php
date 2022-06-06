<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오전 11:30
 */

namespace Amore\PointsIntegration\Model;

use Amore\PointsIntegration\Model\Source\Config;
use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Amore\PointsIntegration\Logger\Logger;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Eguana\RedInvoice\Model\ResourceModel\RedInvoice\CollectionFactory as RedInvoiceCollectionFactory;

class PosOrderData
{
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
        Logger                         $pointsIntegrationLogger
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
        $redInvoiceData = [];
        $redInvoice = $this->getDataRedInvoice($order->getEntityId());
        if($redInvoice->getId()) {
            $redInvoiceData = [
                'company' => $redInvoice->getCompanyName(),
                'taxID' => $redInvoice->getTaxCode(),
                'address' => $redInvoice->getRoadName() . ' ' . $redInvoice->getCity() . ' ' . $redInvoice->getState(),
                'email' => $order->getCustomerEmail(),
                'mobile' => $order->getShippingAddress()->getTelephone(),
            ];
        }
        $orderData = [
            'salOrgCd' => $this->config->getOrganizationSalesCode($websiteId),
            'salOffCd' => $this->config->getOfficeSalesCode($websiteId),
            'saledate' => $this->dateFormat($order->getCreatedAt()),
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
     * @throws \Exception
     */
    public function getItemData(Order $order)
    {
        $orderItemData = [];
        $itemsSubtotal = 0;
        $itemsDiscountAmount = 0;
        $itemsGrandTotal = 0;
        $skuPrefix = $this->getSKUPrefix($order->getStoreId()) ?: '';
        $orderItems = $order->getAllVisibleItems();

        /** @var Item $orderItem */
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() != 'bundle') {

                $itemSubtotal = $this->simpleAndConfigurableSubtotal($orderItem);
                $itemTotalDiscount = $this->simpleAndConfigurableTotalDiscount($orderItem);
                $itemGrandTotal = $itemSubtotal - $itemTotalDiscount;
                $stripSku = str_replace($skuPrefix, '', $orderItem->getSku());

                $orderItemData[] = [
                    'prdCD' => $stripSku,
                    'qty' => (int)$orderItem->getQtyOrdered(),
                    'price' => (int)$orderItem->getOriginalPrice(),
                    'salAmt' => (int)$itemSubtotal,
                    'dcAmt' => (int)$itemTotalDiscount,
                    'netSalAmt' => (int)$itemGrandTotal
                ];

                $itemsSubtotal += $itemSubtotal;
                $itemsDiscountAmount += $itemTotalDiscount;
                $itemsGrandTotal += $itemGrandTotal;
            } else {
                /** @var \Magento\Catalog\Model\Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId());
                $bundleChildren = $orderItem->getChildrenItems();
                $bundlePriceType = $bundleProduct->getPriceType();

                foreach ($bundleChildren as $bundleChild) {
                    $stripSku = str_replace($skuPrefix, '', $bundleChild->getSku());
                    if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                        $bundleChildPrice = $this->productRepository->get($bundleChild->getSku(), false, $order->getStoreId())->getPrice();
                    } else {
                        $bundleChildPrice = $bundleChild->getOriginalPrice();
                    }

                    $product = $this->productRepository->get($bundleChild->getSku(), false, $order->getStoreId());
                    $bundleChildSubtotal = $this->bundleChildSubtotal($bundleChildPrice, $bundleChild);
                    $bundleChildDiscountAmount = $this->getBundleChildDiscountAmount($bundlePriceType, $orderItem, $bundleChild);
                    $priceGap = $orderItem->getOriginalPrice() - $orderItem->getPrice();

                    $qtyPerBundle = $bundleChild->getQtyOrdered() / $orderItem->getQtyOrdered();
                    $childPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getOriginalPrice()) / $qtyPerBundle;
                    $catalogRuledPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, ($priceGap)) / $qtyPerBundle;
                    $itemTotalDiscount = abs(round(
                        $bundleChildDiscountAmount +
                        (($product->getPrice() - $childPriceRatio) * $bundleChild->getQtyOrdered()) +
                        $catalogRuledPriceRatio * $bundleChild->getQtyOrdered()
                    ));

                    $bundleChildGrandTotal = $bundleChildSubtotal - $itemTotalDiscount;

                    $orderItemData[] = [
                        'prdCD' => $stripSku,
                        'qty' => (int)$bundleChild->getQtyOrdered(),
                        'price' => (int)$bundleChildPrice,
                        'salAmt' => (int)$bundleChildSubtotal,
                        'dcAmt' => (int)$itemTotalDiscount,
                        'netSalAmt' => (int)$bundleChildGrandTotal
                    ];

                    $itemsSubtotal += $bundleChildSubtotal;
                    $itemsDiscountAmount += $itemTotalDiscount;
                    $itemsGrandTotal += $bundleChildGrandTotal;
                }
            }
        }

        $orderSubtotal = $this->getOrderSubtotal($order);
        $orderGrandTotal = $this->getOrderGrandTotal($order);
        $orderDiscount = $orderSubtotal - $orderGrandTotal;

        $orderItemData = $this->priceCorrector($orderSubtotal, $itemsSubtotal, $orderItemData, 'salAmt');
        $orderItemData = $this->priceCorrector($orderDiscount, $itemsDiscountAmount, $orderItemData, 'dcAmt');
        $orderItemData = $this->priceCorrector($orderGrandTotal, $itemsGrandTotal, $orderItemData, 'netSalAmt');

        if (count($orderItems) > count($orderItemData)) {
            throw new \Exception('Missing items');
        }

        return $orderItemData;
    }

    public function priceCorrector($orderAmount, $itemTotalAmount, $orderItemData, $field)
    {
        if ($orderAmount != $itemTotalAmount) {
            $amountDifference = $orderAmount - $itemTotalAmount;

            foreach ($orderItemData as $key => $value) {
                if ($value['price'] == 0) {
                    continue;
                }
                $orderItemData[$key][$field] = $value[$field] + $amountDifference;
                break;
            }
        }

        return $orderItemData;
    }

    /**
     * @param Order $order
     * @return float
     * @throws \Exception
     */
    public function getOrderSubtotal($order)
    {
        return round($order->getSubtotal() + $this->getBundleExtraAmount($order) + $this->getCatalogRuleDiscountAmount($order));
    }

    /**
     * @param Order $order
     * @return float|null
     */
    public function getOrderGrandTotal(Order $order)
    {
        return $order->getGrandTotal() == 0 ? $order->getGrandTotal() : round($order->getGrandTotal() - $order->getShippingAmount());
    }

    /**
     * @param $order Order
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function getBundleExtraAmount($order)
    {
        $orderItems = $order->getAllVisibleItems();
        $priceDifferences = 0;

        /** @var Item $orderItem */
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() == 'bundle') {
                /** @var \Magento\Catalog\Model\Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId(), false, $order->getStoreId());
                $bundlePriceType = $bundleProduct->getPriceType();

                if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
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
     * @throws \Exception
     */
    public function getCatalogRuleDiscountAmount($order)
    {
        $catalogRuleDiscount = 0;
        $orderItems = $order->getAllVisibleItems();
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() != 'bundle') {
                $catalogRuleDiscount += ($orderItem->getOriginalPrice() - $orderItem->getPrice()) * $orderItem->getQtyOrdered();
            } else {
                /** @var \Magento\Catalog\Model\Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId(), false, $order->getStoreId());
                $bundlePriceType = $bundleProduct->getPriceType();

                if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
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
     * @return float|int
     */
    public function simpleAndConfigurableSubtotal(Item $orderItem)
    {
        return abs(round($orderItem->getOriginalPrice() * $orderItem->getQtyOrdered()));
    }

    /**
     * @param Item $orderItem
     * @return float|int
     * @throw NoSuchEntityException
     */
    public function simpleAndConfigurableTotalDiscount(Item $orderItem)
    {
        return abs(round($orderItem->getDiscountAmount() + (($orderItem->getOriginalPrice() - $orderItem->getPrice()) * $orderItem->getQtyOrdered())));
    }

    public function bundleChildSubtotal($bundleChildPrice, $bundleChildFromOrder)
    {
        return abs(round($bundleChildPrice * $bundleChildFromOrder->getQtyOrdered()));
    }

    /**
     * Get new bundle child discount amount
     *
     * @param string $bundlePriceType
     * @param Item $orderItem
     * @param Item $bundleChild
     * @return float
     * @throws NoSuchEntityException
     */
    public function getBundleChildDiscountAmount($bundlePriceType, $orderItem, $bundleChild)
    {
        $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
            round($this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getDiscountAmount())) :
            round($bundleChild->getDiscountAmount());

        return $bundleChildDiscountAmount;
    }
    /**
     * @param $orderId
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder($orderId)
    {
        return $this->orderRepository->get($orderId);
    }

    /**
     * Get proportion of bundle child
     *
     * @param Item $orderItem
     * @param \Magento\Sales\Model\Order\Item $bundleChild
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
     * @throws \Exception
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
            $order->setData('pos_order_paid_sent', true);
            $order->setData('pos_order_paid_send', false);
            $comment = __('Send paid info to POS successfully');
            $order->addCommentToStatusHistory($comment);
            $this->orderRepository->save($order);
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
        } catch (\Exception $exception) {
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
        $orderCollection
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('pos_order_paid_send', true);

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
            ->addFieldToFilter('pos_order_cancel_send', true);

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
}
