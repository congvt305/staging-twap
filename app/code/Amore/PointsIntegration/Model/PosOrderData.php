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

        return [
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
                $bundleChildren = $this->getBundleChildren($orderItem->getSku(), $order->getStoreId());
                $bundlePriceType = $bundleProduct->getPriceType();

                $oddToTal = 0;
                $bundleChildSkus = [];
                foreach ($bundleChildren as $bundleChild) {
                    $itemId = $orderItem->getItemId();
                    $stripSku = str_replace($skuPrefix, '', $bundleChild->getSku());
                    $bundleChildFromOrder = $this->getBundleChildFromOrder($itemId, $bundleChild->getSku());
                    if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                        $bundleChildPrice = $this->productRepository->get($bundleChild->getSku(), false, $order->getStoreId())->getPrice();
                    } else {
                        $bundleChildPrice = $bundleChildFromOrder->getOriginalPrice();
                    }

                    $product = $this->productRepository->get($bundleChild->getSku(), false, $order->getStoreId());
                    $bundleChildSubtotal = $this->bundleChildSubtotal($bundleChildPrice, $bundleChildFromOrder);
                    $bundleChildDiscountAmount = $this->bundleChildDiscountAmount($bundlePriceType, $orderItem, $bundleChild);

                    $priceGap = $orderItem->getOriginalPrice() - $orderItem->getPrice();
                    $childPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getOriginalPrice()) / $bundleChild->getQty();
                    $catalogRuledPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, ($priceGap)) / $bundleChild->getQty();

                    $itemTotalDiscount = abs(
                        $bundleChildDiscountAmount +
                        (($product->getPrice() - $childPriceRatio) * $bundleChildFromOrder->getQtyOrdered()) +
                        $catalogRuledPriceRatio * $bundleChildFromOrder->getQtyOrdered()
                    );
                    $oddToTal += $itemTotalDiscount - (int) $itemTotalDiscount;
                    $itemTotalDiscount = (int) $itemTotalDiscount;
                    $bundleChildSkus[] = $stripSku;

                    $bundleChildGrandTotal = $bundleChildSubtotal - $itemTotalDiscount;

                    $orderItemData[] = [
                        'prdCD' => $stripSku,
                        'qty' => (int)$bundleChildFromOrder->getQtyOrdered(),
                        'price' => (int)$bundleChildPrice,
                        'salAmt' => (int)$bundleChildSubtotal,
                        'dcAmt' => (int)$itemTotalDiscount,
                        'netSalAmt' => (int)$bundleChildGrandTotal
                    ];

                    $itemsSubtotal += $bundleChildSubtotal;
                    $itemsDiscountAmount += $itemTotalDiscount;
                    $itemsGrandTotal += $bundleChildGrandTotal;
                }
                if ($oddToTal > 0) {
                    foreach ($orderItemData as $key => $orderItemDataCalculation) {
                        if (isset($orderItemDataCalculation['prdCD']) && in_array($orderItemDataCalculation['prdCD'], $bundleChildSkus)
                            && isset($orderItemDataCalculation['dcAmt']) && $orderItemDataCalculation['dcAmt'] > 0
                        ) {
                            $orderItemDataCalculation['dcAmt'] += round($oddToTal);
                            $orderItemDataCalculation['netSalAmt'] -= round($oddToTal);
                            $orderItemData[$key] = $orderItemDataCalculation;
                            $itemsDiscountAmount += round($oddToTal);
                            $itemsGrandTotal -= round($oddToTal);
                            break;
                        }
                    }
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
                $bundleChildren = $this->getBundleChildren($orderItem->getSku(), $order->getStoreId());
                $bundlePriceType = $bundleProduct->getPriceType();

                if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    foreach ($bundleChildren as $bundleChild) {
                        $childPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getOriginalPrice()) / $bundleChild->getQty();
                        $originItemPrice = $this->productRepository->get($bundleChild->getSku(), false, $order->getStoreId())->getPrice();
                        $bundleChildByOrder = $this->getBundleChildFromOrder($orderItem->getItemId(), $bundleChild->getSku());

                        $priceDifferences += (($originItemPrice - $childPriceRatio) * $bundleChildByOrder->getQtyOrdered());
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
                $bundleChildren = $this->getBundleChildren($orderItem->getSku(), $order->getStoreId());
                $bundlePriceType = $bundleProduct->getPriceType();

                if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    foreach ($bundleChildren as $bundleChild) {
                        $bundleChildByOrder = $this->getBundleChildFromOrder($orderItem->getItemId(), $bundleChild->getSku());
                        $catalogRuledPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, ($orderItem->getOriginalPrice() - $orderItem->getPrice())) / $bundleChild->getQty();

                        $catalogRuleDiscount += $catalogRuledPriceRatio * $bundleChildByOrder->getQtyOrdered();
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
     * @param string $bundlePriceType
     * @param Item $orderItem
     * @param LinkInterface $bundleChild
     * @return float
     * @throws NoSuchEntityException
     */
    public function bundleChildDiscountAmount($bundlePriceType, $orderItem, LinkInterface $bundleChild)
    {
        $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
            $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getDiscountAmount()) :
            $this->getBundleChildFromOrder($orderItem->getItemId(), $bundleChild->getSku())->getDiscountAmount();

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

    public function getBundleChildFromOrder($itemId, $bundleChildSku)
    {
        $bundleChild = null;
        /** @var Item $itemOrdered */
        $itemOrdered = $this->orderItemRepository->get($itemId);

        $childrenItems = $itemOrdered->getChildrenItems();
        /** @var Item $childItem */
        foreach ($childrenItems as $childItem) {
            if ($childItem->getSku() == $bundleChildSku) {
                $bundleChild = $childItem;
                break;
            }
        }
        return $bundleChild;
    }

    public function getBundleChildren($bundleDynamicSku, $storeId)
    {
        $skuPrefix = $this->getSKUPrefix($storeId) ?: '';
        $bundleSku = explode("-", $bundleDynamicSku);
        if ($skuPrefix && strpos($skuPrefix, '-') !== false) {
            $bundleSku = $skuPrefix . $bundleSku[1];
        } else {
            $bundleSku = $bundleSku[0];
        }

        try {
            return $this->productLinkManagement->getChildren($bundleSku);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @param Item $orderItem
     * @param LinkInterface $bundleChild
     * @param float $valueToCalculate
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getProportionOfBundleChild($orderItem, $bundleChild, $valueToCalculate)
    {
        $bundleChildrenTotalAmount = $this->getBundleChildrenTotalAmount($orderItem);

        $bundleChildPrice = $this->productRepository->get($bundleChild->getSku(), false, $orderItem->getStoreId())->getPrice();
        $rate = ($bundleChildPrice / $bundleChildrenTotalAmount) * $bundleChild->getQty();

        return $valueToCalculate * $rate;
    }

    /**
     * @param Item $orderItem
     * @return float|null
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function getBundleChildrenTotalAmount(Item $orderItem)
    {
        $originalPriceTotal = 0;

        $childrenItems = $this->getBundleChildren($orderItem->getSku(), $orderItem->getStoreId());

        /** @var LinkInterface $childItem */
        foreach ($childrenItems as $childItem) {
            $originalProductPrice = $this->productRepository->get($childItem->getSku(), false, $orderItem->getStoreId())->getPrice();
            $originalPriceTotal += ($originalProductPrice * $childItem->getQty());
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
