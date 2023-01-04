<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-17
 * Time: 오후 5:20
 */

namespace Amore\PointsIntegration\Model;

use Amore\PointsIntegration\Model\Source\Config;
use CJ\Middleware\Helper\Data;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Rma\Api\Data\CommentInterfaceFactory;
use Magento\Rma\Api\Data\RmaInterface;
use Magento\Rma\Api\RmaRepositoryInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use Amore\PointsIntegration\Logger\Logger;
use Magento\Store\Model\ScopeInterface;

class PosReturnData
{
    const POS_ORDER_TYPE_RETURN = '000020';
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
     * @var StoreRepositoryInterface
     */
    private $storeRepository;
    /**
     * @var RmaRepositoryInterface
     */
    private $rmaRepository;
    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var ProductLinkManagementInterface
     */
    private $productLinkManagement;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
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
    private $itemCollectionFactory;

    /**
     * @var \Magento\Rma\Model\ResourceModel\Rma\CollectionFactory
     */
    private $rmaCollectionFactory;

    /**
     * @var Logger
     */
    private $pointsIntegrationLogger;

    /**
     * @var CommentInterfaceFactory
     */
    private $commentInterfaceFactory;

    /**
     * @var Data
     */
    private $middlewareHelper;


    /**
     * @var \Amasty\Rewards\Model\Config
     */
    private $amConfig;

    /**
     * @param Config $config
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param RmaRepositoryInterface $rmaRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ProductLinkManagementInterface $productLinkManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param DateTime $dateTime
     * @param CollectionFactory $itemCollectionFactory
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Rma\Model\ResourceModel\Rma\CollectionFactory $rmaCollectionFactory
     * @param Logger $pointsIntegrationLogger
     * @param CommentInterfaceFactory $commentInterfaceFactory
     * @param Data $middlewareHelper
     */
    public function __construct(
        Config                                                 $config,
        SearchCriteriaBuilder                                  $searchCriteriaBuilder,
        OrderRepositoryInterface                               $orderRepository,
        StoreRepositoryInterface                               $storeRepository,
        RmaRepositoryInterface                                 $rmaRepository,
        OrderItemRepositoryInterface                           $orderItemRepository,
        ProductRepositoryInterface                             $productRepository,
        ProductLinkManagementInterface                         $productLinkManagement,
        CustomerRepositoryInterface                            $customerRepository,
        DateTime                                               $dateTime,
        CollectionFactory                                      $itemCollectionFactory,
        ResourceConnection                                     $resourceConnection,
        \Magento\Rma\Model\ResourceModel\Rma\CollectionFactory $rmaCollectionFactory,
        Logger                                                 $pointsIntegrationLogger,
        CommentInterfaceFactory                                $commentInterfaceFactory,
        Data                                                   $middlewareHelper,
        \Amasty\Rewards\Model\Config $amConfig
    ) {
        $this->config = $config;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->storeRepository = $storeRepository;
        $this->rmaRepository = $rmaRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->productRepository = $productRepository;
        $this->productLinkManagement = $productLinkManagement;
        $this->customerRepository = $customerRepository;
        $this->dateTime = $dateTime;
        $this->resourceConnection = $resourceConnection;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->rmaCollectionFactory = $rmaCollectionFactory;
        $this->pointsIntegrationLogger = $pointsIntegrationLogger;
        $this->commentInterfaceFactory = $commentInterfaceFactory;
        $this->middlewareHelper = $middlewareHelper;
        $this->amConfig = $amConfig;
    }

    /**
     * Get rma data
     *
     * @param RmaInterface $rma
     */
    public function getRmaData($rma)
    {
        $order = $rma->getOrder();
        $websiteId = $order->getStore()->getWebsiteId();
        $customer = $order->getCustomerId() ? $this->getCustomer($rma->getCustomerId()) : null;
        $posIntegrationNumber = $customer && $customer->getCustomAttribute('integration_number') ?
            $customer->getCustomAttribute('integration_number')->getValue() : null;

        $rmaItem = $this->getRmaItemData($rma);
        $invoice = $order->getInvoiceCollection()->getFirstItem();
        $couponCode = $order->getCouponCode();

        $redemptionFlag = 'N';
        $rewardPoints = 0;
        $storeId = $order->getStoreId();
        if($this->amConfig->isEnabled($storeId)) {
            if ($order->getData('am_spent_reward_points')) {
                $rewardPoints = $this->roundingPrice($order->getData('am_spent_reward_points'));
            }

            $spendingRate = $this->amConfig->getPointsRate($storeId);
            if (!$spendingRate) {
                $spendingRate = 1;
            }
            $discountFromPoints = $rewardPoints / $spendingRate;
            if (($order->getGrandTotal() - $order->getShippingAmount()) == $discountFromPoints) {
                $redemptionFlag = 'Y';
            }
        }

        $rmaData = [
            'salOrgCd' => $this->config->getOrganizationSalesCode($websiteId),
            'salOffCd' => $this->config->getOfficeSalesCode($websiteId),
            'saledate' => $this->dateFormat($rma->getDateRequested()),
            'orderID' => 'R' . $order->getIncrementId(),
            'rcptNO' => 'I' . $invoice->getIncrementId(),
            'cstmIntgSeq' => $posIntegrationNumber,
            'orderType' => self::POS_ORDER_TYPE_RETURN,
            'promotionKey' => $couponCode,
            'orderInfo' => $rmaItem,
            'PointAccount' => (int)$rewardPoints,
            'redemptionFlag' => $redemptionFlag
        ];

        return $rmaData;
    }

    /**
     * Get rma data
     *
     * @param RmaInterface $rma
     */
    public function getRmaItemData($rma)
    {
        $rmaItemData = [];
        $rmaItems = $rma->getItems();
        $order = $rma->getOrder();
        $skuPrefix = $this->getSKUPrefix($order->getStoreId()) ?: '';

        $itemsSubtotal = 0;
        $itemsDiscountAmount = 0;
        $itemsGrandTotal = 0;
        $isDecimalFormat = $this->middlewareHelper->getIsDecimalFormat('store', $order->getStoreId());

        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() != 'bundle') {
                $itemGrandTotal = $orderItem->getRowTotal() - $orderItem->getDiscountAmount();
                $itemSubtotal = abs($this->roundingPrice($orderItem->getOriginalPrice() * $rmaItem->getQtyRequested(), $isDecimalFormat));
                $itemTotalDiscount = abs($this->roundingPrice($this->getRateAmount($orderItem->getDiscountAmount(), $orderItem->getQtyOrdered(), $rmaItem->getQtyRequested())
                    + (($orderItem->getOriginalPrice() - $orderItem->getPrice()) * $rmaItem->getQtyRequested()), $isDecimalFormat));

                $stripSku = str_replace($skuPrefix, '', $orderItem->getSku());

                $rmaItemData[] = [
                    'prdCD' => $stripSku,
                    'qty' => (int)$rmaItem->getQtyRequested(),
                    'price' => $this->convertPrice($orderItem->getOriginalPrice(), $isDecimalFormat),
                    'salAmt' => $this->convertPrice($itemSubtotal, $isDecimalFormat),
                    'dcAmt' => $this->convertPrice($itemTotalDiscount, $isDecimalFormat),
                    'netSalAmt' => $this->convertPrice($itemGrandTotal, $isDecimalFormat)
                ];

                $itemsSubtotal += $itemSubtotal;
                $itemsGrandTotal += ($itemSubtotal - $itemTotalDiscount);
                $itemsDiscountAmount += $itemTotalDiscount;
            } else {
                /** @var \Magento\Catalog\Model\Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId());
                $bundleChildren = $orderItem->getChildrenItems();
                $bundlePriceType = $bundleProduct->getPriceType();

                foreach ($bundleChildren as $bundleChildrenItem) {
                    $product = $this->productRepository->get($bundleChildrenItem->getSku(), false, $rma->getStoreId());

                    if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                        $bundleChildPrice = $this->productRepository->get($bundleChildrenItem->getSku(), false, $order->getStoreId())->getPrice();
                    } else {
                        $bundleChildPrice = $bundleChildrenItem->getOriginalPrice();
                    }
                    $qtyPerBundle = $bundleChildrenItem->getQtyOrdered() / $orderItem->getQtyOrdered();
                    $itemSubtotal = abs($this->roundingPrice($product->getPrice() * $rmaItem->getQtyRequested() * $qtyPerBundle, $isDecimalFormat));
                    $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
                        $this->getProportionOfBundleChild($orderItem, $bundleChildrenItem, $orderItem->getDiscountAmount()) :
                        $bundleChildrenItem->getDiscountAmount();

                    $product = $this->productRepository->get($bundleChildrenItem->getSku(), false, $rma->getStoreId());
                    $childPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChildrenItem, $orderItem->getOriginalPrice()) / $qtyPerBundle;
                    $catalogRuledPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChildrenItem, ($orderItem->getOriginalPrice() - $orderItem->getPrice())) / $qtyPerBundle;
                    $itemTotalDiscount = abs($this->roundingPrice(
                        $this->getRateAmount($bundleChildDiscountAmount, $bundleChildrenItem->getQtyOrdered(), $rmaItem->getQtyRequested() * $qtyPerBundle)
                        + (($product->getPrice() - $childPriceRatio) * $rmaItem->getQtyRequested() * $qtyPerBundle)
                        + $catalogRuledPriceRatio * $rmaItem->getQtyRequested() * $qtyPerBundle
                        , $isDecimalFormat));
                    $stripSku = str_replace($skuPrefix, '', $bundleChildrenItem->getSku());

                    $rmaItemData[] = [
                        'prdCD' => $stripSku,
                        'qty' => (int)$rmaItem->getQtyRequested(),
                        'price' => $this->convertPrice($bundleChildPrice, $isDecimalFormat),
                        'salAmt' => $this->convertPrice($itemSubtotal, $isDecimalFormat),
                        'dcAmt' => $this->convertPrice($itemTotalDiscount, $isDecimalFormat),
                        'netSalAmt' => $this->convertPrice($itemSubtotal - $itemTotalDiscount, $isDecimalFormat)
                    ];

                    $itemsSubtotal += $itemSubtotal;
                    $itemsGrandTotal += ($itemSubtotal - $itemTotalDiscount);
                    $itemsDiscountAmount += $itemTotalDiscount;
                }
            }
        }
        $orderSubtotal = $this->roundingPrice($this->getRmaSubtotal($rma), $isDecimalFormat);
        $orderGrandTotal = $order->getGrandTotal() == 0 ? $order->getGrandTotal() : $this->roundingPrice($this->getRmaGrandTotal($rma), $isDecimalFormat);
        $orderDiscountAmount = $this->roundingPrice($this->getRmaDiscountAmount($rma) + $this->getBundleExtraAmount($rma) + $this->getCatalogRuleDiscountAmount($rma), $isDecimalFormat);

        $rmaItemData = $this->priceCorrector($orderSubtotal, $itemsSubtotal, $rmaItemData, 'salAmt', $isDecimalFormat);
        $rmaItemData = $this->priceCorrector($orderDiscountAmount, $itemsDiscountAmount, $rmaItemData, 'dcAmt', $isDecimalFormat);
        $rmaItemData = $this->priceCorrector($orderGrandTotal, $itemsGrandTotal, $rmaItemData, 'netSalAmt', $isDecimalFormat);

        if ($isDecimalFormat) {
            $listToFormat = ['salAmt', 'dcAmt', 'netSalAmt', 'price'];

            foreach ($listToFormat as $field) {
                foreach ($rmaItemData as $key => $value) {
                    if (isset($value[$field]) && (is_float($value[$field]) || is_int($value[$field]))) {
                        $rmaItemData[$key][$field] = $this->formatPrice($value[$field], $isDecimalFormat);
                    }
                }
            }
        }

        return $rmaItemData;
    }

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
     * @param \Magento\Rma\Model\Rma $rma
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function getCatalogRuleDiscountAmount($rma)
    {
        $catalogRuleDiscount = 0;
        $order = $rma->getOrder();
        $rmaItems = $rma->getItems();
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() != 'bundle') {
                $catalogRuleDiscount += ($orderItem->getOriginalPrice() - $orderItem->getPrice()) * $rmaItem->getQtyRequested();
            } else {
                /** @var \Magento\Catalog\Model\Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId(), false, $order->getStoreId());
                $bundlePriceType = $bundleProduct->getPriceType();

                if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    foreach ($orderItem->getChildrenItems() as $bundleChild) {
                        $qtyPerBundle = $bundleChild->getQtyOrdered() / $orderItem->getQtyOrdered();
                        $catalogRuledPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, ($orderItem->getOriginalPrice() - $orderItem->getPrice())) / $qtyPerBundle;

                        $catalogRuleDiscount += $catalogRuledPriceRatio * $rmaItem->getQtyRequested() * $qtyPerBundle;
                    }
                }
            }
        }
        return $catalogRuleDiscount;
    }

    /**
     * @param $rma \Magento\Rma\Model\Rma
     */
    public function getBundleExtraAmount($rma)
    {
        $rmaItems = $rma->getItems();
        $priceDifferences = 0;

        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() == 'bundle') {
                /** @var \Magento\Catalog\Model\Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId());
                $bundlePriceType = $bundleProduct->getPriceType();

                if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    foreach ($orderItem->getChildrenItems() as $bundleChild) {
                        $qtyPerBundle = $bundleChild->getQtyOrdered() / $orderItem->getQtyOrdered();

                        $childPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getOriginalPrice()) / $qtyPerBundle;
                        $originItemPrice = $this->productRepository->get($bundleChild->getSku(), false, $rma->getStoreId())->getPrice();

                        $priceDifferences += (($originItemPrice - $childPriceRatio) * $rmaItem->getQtyRequested() * $qtyPerBundle);
                    }
                }
            }
        }
        return $priceDifferences;
    }

    /**
     * Get rma subtotal
     *
     * @param \Magento\Rma\Model\Rma $rma
     */
    public function getRmaSubtotal($rma)
    {
        $subtotal = 0;
        $rmaItems = $rma->getItems();
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() == 'bundle') {
                foreach ($orderItem->getChildrenItems() as $bundleChild) {
                    $qtyPerBundle = $bundleChild->getQtyOrdered() / $orderItem->getQtyOrdered();
                    $product = $this->productRepository->get($bundleChild->getSku(), false, $rma->getStoreId());
                    $subtotal += ($product->getPrice() * $rmaItem->getQtyRequested() * $qtyPerBundle);
                }
            } else {
                $subtotal += ($orderItem->getOriginalPrice() * $rmaItem->getQtyRequested());
            }
        }
        return $subtotal;
    }

    /**
     * Get rma discount amount
     *
     * @param \Magento\Rma\Model\Rma $rma
     */
    public function getRmaDiscountAmount($rma)
    {
        $discountAmount = 0;
        $rmaItems = $rma->getItems();
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() == 'bundle') {
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId());
                $bundlePriceType = $bundleProduct->getPriceType();
                foreach ($orderItem->getChildrenItems() as $bundleChild) {
                    $bundleChildDiscountAmount = $this->getDiscountAmountForBundleChild($bundlePriceType, $orderItem, $bundleChild);
                    $qtyPerBundle = $bundleChild->getQtyOrdered() / $orderItem->getQtyOrdered();
                    $discountAmount += ($bundleChildDiscountAmount * $rmaItem->getQtyRequested() * $qtyPerBundle / $bundleChild->getQtyOrdered());
                }
            } else {
                $discountAmount += ($orderItem->getDiscountAmount() * $rmaItem->getQtyRequested() / $orderItem->getQtyOrdered());
            }
        }
        return $discountAmount;
    }

    /**
     * Get discount amount for bundle child
     *
     * @param int $bundlePriceType
     * @param OrderItemInterface $orderItem
     * @param Item $bundleChild
     * @return float|int|null
     * @throws NoSuchEntityException
     */
    public function getDiscountAmountForBundleChild($bundlePriceType, $orderItem, $bundleChild)
    {
        $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
            $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getDiscountAmount()) :
            $bundleChild->getDiscountAmount();

        return $bundleChildDiscountAmount;
    }

    /**
     * Get rma grand total
     *
     * @param \Magento\Rma\Model\Rma $rma
     * @return int
     */
    public function getRmaGrandTotal($rma)
    {
        $grandTotal = 0;
        $rmaItems = $rma->getItems();

        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() == 'bundle') {

                $bundleProduct = $this->productRepository->getById($orderItem->getProductId());
                $bundlePriceType = $bundleProduct->getPriceType();

                foreach ($orderItem->getChildrenItems() as $bundleChild) {
                    $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
                        $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getDiscountAmount()) :
                        $bundleChild->getDiscountAmount();
                    $itemGrandTotalInclTax = $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getRowTotalInclTax())
                        - $bundleChildDiscountAmount;

                    $qtyPerBundle = $bundleChild->getQtyOrdered() / $orderItem->getQtyOrdered();
                    $grandTotal += $this->getRateAmount($itemGrandTotalInclTax, $bundleChild->getQtyOrdered(), $rmaItem->getQtyRequested() * $qtyPerBundle);
                }
            } else {
                $itemGrandTotal = $orderItem->getRowTotal()
                    - $orderItem->getDiscountAmount();

                $itemGrandTotal = $this->getRateAmount($itemGrandTotal, $orderItem->getQtyOrdered(), $rmaItem->getQtyRequested());
                $grandTotal += $this->getRateAmount($itemGrandTotal, $orderItem->getQtyOrdered(), $rmaItem->getQtyRequested());
            }
        }
        return $grandTotal;
    }

    /**
     *  Get bundle children total amount
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
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

    /**
     * Get proportion for bundle child
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @param \Magento\Sales\Model\Order\Item $bundleChild
     * @param float $valueToCalculate
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getProportionOfBundleChild($orderItem, $bundleChild, $valueToCalculate)
    {
        $originalPriceSum = $this->getSumOfChildrenOriginPrice($orderItem);

        $bundleChildPrice = $this->productRepository->get($bundleChild->getSku(), false, $orderItem->getStoreId())->getPrice();
        $rate = ($bundleChildPrice / $originalPriceSum) * ($bundleChild->getQtyOrdered() / $orderItem->getQtyOrdered());

        return $valueToCalculate * $rate;
    }

    /**
     * @param $orderItem \Magento\Sales\Api\Data\OrderItemInterface
     */
    public function productTypeCheck($orderItem)
    {
        $simpleItemCollection = $this->getOrderChildItemCollection($orderItem->getOrderId(), $orderItem->getSku());
        $simpleItem = $simpleItemCollection->getFirstItem();

        if ($orderItem->getProductType() == "simple") {
            return $orderItem;
        } else {
            return $simpleItem;
        }
    }

    public function getOrderChildItemCollection($orderId, $sku)
    {
        /** @var Collection $collection */
        $collection = $this->itemCollectionFactory->create();

        $collection->addFieldToFilter('order_id', ['eq' => $orderId])
            ->addFieldToFilter('sku', ['eq' => $sku])
            ->addFieldToFilter('product_type', ['eq' => 'simple'])
            ->addFieldToSelect(["item_id", "order_id", "parent_item_id", "store_id", "product_id", "sku"]);

        return $collection;
    }

    public function getRateAmount($orderItemAmount, $orderItemQty, $rmaItemQty)
    {
        return $orderItemAmount * ($rmaItemQty / $orderItemQty);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     */
    public function getNetQty($orderItem)
    {
        return $orderItem->getQtyOrdered() - $orderItem->getQtyRefunded() - $orderItem->getQtyReturned();
    }

    public function dateFormat($date)
    {
        return date("Ymd", strtotime($date));
    }

    public function getCustomer($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * @param $storeId
     * @return DataObject[]
     */
    public function getCompletedReturnToPOS($storeId): array
    {
        $rmaCollection = $this->rmaCollectionFactory->create();
        $rmaCollection->addFieldToFilter('pos_rma_completed_send', true)
            ->addFieldToFilter('store_id', $storeId);

        return $rmaCollection->getItems();
    }

    /**
     * @param RmaInterface $rma
     */
    public function updatePosReturnOrderSendFlag(RmaInterface $rma)
    {
        try {
            $rma->setData('pos_rma_completed_sent', true);
            $rma->setData('pos_rma_completed_send', false);
            $comment = $this->commentInterfaceFactory->create();
            $comment->setRmaEntityId($rma->getEntityId());
            $comment->setComment(__('Send return info to POS successfully'));
            $comment->setIsAdmin(true);
            $rma->setComments([$comment]);
            $this->rmaRepository->save($rma);
        } catch (\Exception $exception) {
            $this->pointsIntegrationLogger->err($exception->getMessage());
        }
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

    /**
     * @param $price
     * @param $isDecimal
     * @return float|string
     */
    public function formatPrice($price, $isDecimal = false)
    {
        if ($isDecimal) {
            return number_format($price, 2, '.', '');
        }
        return $price;
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

    /**
     * @param $price
     * @param $isDecimal
     * @return int|mixed
     */
    public function convertPrice($price, $isDecimal = false)
    {
        if ($isDecimal) {
            return $price;
        }

        return (int)$price;
    }
}
