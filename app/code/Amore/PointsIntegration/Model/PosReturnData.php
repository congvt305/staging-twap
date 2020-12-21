<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-17
 * Time: 오후 5:20
 */

namespace Amore\PointsIntegration\Model;


use Amore\PointsIntegration\Model\Source\Config;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Rma\Api\RmaRepositoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Store\Api\StoreRepositoryInterface;

class PosReturnData
{
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
     * PosReturnData constructor.
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
     */
    public function __construct(
        Config $config,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        StoreRepositoryInterface $storeRepository,
        RmaRepositoryInterface $rmaRepository,
        OrderItemRepositoryInterface $orderItemRepository,
        ProductRepositoryInterface $productRepository,
        ProductLinkManagementInterface $productLinkManagement,
        CustomerRepositoryInterface $customerRepository,
        DateTime $dateTime,
        CollectionFactory $itemCollectionFactory,
        ResourceConnection $resourceConnection
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
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     */
    public function getRmaData($rma)
    {
        $order = $rma->getOrder();
        $websiteId = $order->getStore()->getWebsiteId();
        $customer = $order->getCustomerId() ? $this->getCustomer($rma->getCustomerId()) : null;
        $posIntegrationNumber = $rma->getCustomerId() ? $customer->getCustomAttribute('integration_number')->getValue() : null;

        $rmaItem = $this->getRmaItemData($rma);

        $rmaData = [
            'salOrgCd' => $this->config->getOrganizationSalesCode($websiteId),
            'salOffCd' => $this->config->getOfficeSalesCode($websiteId),
            'saledate' => $this->dateFormat($rma->getDateRequested()),
            'orderID' => 'R'.$rma->getIncrementId(),
            'rcptNO' => 'R'.$rma->getIncrementId(),
            'cstmIntgSeq' => $posIntegrationNumber,
            'orderType' => '000020',
            'promotionKey' => '',
            'orderInfo' => $rmaItem
        ];

        return $rmaData;
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     */
    public function getRmaItemData($rma)
    {
        $rmaItemData = [];
        $storeId = $rma->getStoreId();
        $rmaItems = $rma->getItems();
        $order = $rma->getOrder();

        $itemsSubtotal = 0;
        $itemsDiscountAmount = 0;
        $itemsGrandTotal = 0;

        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() != 'bundle') {
                $itemGrandTotal = $orderItem->getRowTotal() - $orderItem->getDiscountAmount();
                $itemSubtotal = abs(round($orderItem->getOriginalPrice() * $rmaItem->getQtyRequested()));
                $itemTotalDiscount = abs(round($this->getRateAmount($orderItem->getDiscountAmount(), $this->getNetQty($orderItem), $rmaItem->getQtyRequested())
                    + (($orderItem->getOriginalPrice() - $orderItem->getPrice()) * $rmaItem->getQtyRequested())));

                $product = $this->productRepository->get($rmaItem->getProductSku(), false, $storeId);

                $rmaItemData[] = [
                    'prdCD' => $this->productTypeCheck($orderItem)->getSku(),
                    'qty' => (int)$rmaItem->getQtyRequested(),
                    'price' => (int)$orderItem->getOriginalPrice(),
                    'salAmt' => (int)$itemSubtotal,
                    'dcAmt' => (int)$itemTotalDiscount,
                    'netSalAmt' => (int)$itemGrandTotal
                ];

                $itemsSubtotal += $itemSubtotal;
                $itemsGrandTotal += ($itemSubtotal - $itemTotalDiscount);
                $itemsDiscountAmount += $itemTotalDiscount;
            } else {
                /** @var \Magento\Catalog\Model\Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId());
                $bundleChildren = $this->getBundleChildren($orderItem->getSku());
                $bundlePriceType = $bundleProduct->getPriceType();

                foreach ($bundleChildren as $bundleChildrenItem) {
                    $itemId = $rmaItem->getOrderItemId();
                    $bundleChildFromOrder = $this->getBundleChildFromOrder($itemId, $bundleChildrenItem->getSku());
                    $product = $this->productRepository->get($bundleChildrenItem->getSku(), false, $rma->getStoreId());

                    if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                        $bundleChildPrice = $this->productRepository->get($bundleChildrenItem->getSku(), false, $order->getStoreId())->getPrice();
                    } else {
                        $bundleChildPrice = $bundleChildFromOrder->getOriginalPrice();
                    }

                    $itemSubtotal = abs(round($product->getPrice() * $rmaItem->getQtyRequested() * $bundleChildrenItem->getQty()));
                    $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
                        $this->getProportionOfBundleChild($orderItem, $bundleChildrenItem, $orderItem->getDiscountAmount()) :
                        $bundleChildFromOrder->getDiscountAmount();
                    $itemGrandTotal = $this->getProportionOfBundleChild($orderItem, $bundleChildrenItem, $orderItem->getRowTotal())
                        - $bundleChildDiscountAmount;

                    $product = $this->productRepository->get($bundleChildrenItem->getSku(), false, $rma->getStoreId());
                    $childPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChildrenItem, ($orderItem->getOriginalPrice() - $orderItem->getPrice())) / $bundleChildrenItem->getQty();
                    $itemTotalDiscount = abs(round($this->getRateAmount($bundleChildDiscountAmount, $this->getNetQty($bundleChildFromOrder), $rmaItem->getQtyRequested() * $bundleChildrenItem->getQty())
                        + (($product->getPrice() - $childPriceRatio) * $rmaItem->getQtyRequested() * $bundleChildrenItem->getQty())));

                    $rmaItemData[] = [
                        'prdCD' => $bundleChildrenItem->getSku(),
                        'qty' => (int)$rmaItem->getQtyRequested(),
                        'price' => (int)$bundleChildPrice,
                        'salAmt' => (int)$itemSubtotal,
                        'dcAmt' => (int)$itemTotalDiscount,
                        'netSalAmt' => (int)$itemGrandTotal
                    ];

                    $itemsSubtotal += $itemSubtotal;
                    $itemsGrandTotal += ($itemSubtotal - $itemTotalDiscount);
                    $itemsDiscountAmount += $itemTotalDiscount;
                }
            }
        }
        $orderSubtotal = round($this->getRmaSubtotal($rma));
        $orderGrandTotal = $order->getGrandTotal() == 0 ? $order->getGrandTotal() : round($this->getRmaGrandTotal($rma));
        $orderDiscountAmount = round($this->getRmaDiscountAmount($rma) + $this->getBundleExtraAmount($rma) + $this->getCatalogRuleDiscountAmount($rma));

        $rmaItemData =  $this->priceCorrector($orderSubtotal, $itemsSubtotal, $rmaItemData, 'salAmt');
        $rmaItemData =  $this->priceCorrector($orderDiscountAmount, $itemsDiscountAmount, $rmaItemData, 'dcAmt');
        $rmaItemData =  $this->priceCorrector($orderGrandTotal, $itemsGrandTotal, $rmaItemData, 'netSalAmt');

        return $rmaItemData;
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
                $bundleChildren = $this->getBundleChildren($orderItem->getSku());
                $bundlePriceType = $bundleProduct->getPriceType();

                if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    foreach ($bundleChildren as $bundleChild) {
                        $catalogRuledPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, ($orderItem->getOriginalPrice() - $orderItem->getPrice())) / $bundleChild->getQty();

                        $catalogRuleDiscount += $catalogRuledPriceRatio * $rmaItem->getQtyRequested() * $bundleChild->getQty();
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
                $bundleChildren = $this->getBundleChildren($orderItem->getSku());
                $bundlePriceType = $bundleProduct->getPriceType();

                if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    foreach ($bundleChildren as $bundleChild) {
                        $childPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getOriginalPrice()) / $bundleChild->getQty();
                        $originItemPrice = $this->productRepository->get($bundleChild->getSku(), false, $rma->getStoreId())->getPrice();
                        $bundleChildByOrder = $this->getBundleChildFromOrder($orderItem->getItemId(), $bundleChild->getSku());

                        $priceDifferences += (($originItemPrice - $childPriceRatio) * $rmaItem->getQtyRequested() * $bundleChild->getQty());
                    }
                }
            }
        }
        return $priceDifferences;
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     */
    public function getRmaSubtotal($rma)
    {
        $subtotal = 0;
        $rmaItems = $rma->getItems();
        foreach ($rmaItems as $rmaItem) {
            $orderItem = $this->orderItemRepository->get($rmaItem->getOrderItemId());
            if ($orderItem->getProductType() == 'bundle') {
                $bundleChildren = $this->getBundleChildren($orderItem->getSku());
                foreach ($bundleChildren as $bundleChild) {
                    $product = $this->productRepository->get($bundleChild->getSku(), false, $rma->getStoreId());
                    $subtotal += ($product->getPrice() * $rmaItem->getQtyRequested() * $bundleChild->getQty());
                }
            } else {
                $subtotal += ($orderItem->getOriginalPrice() * $rmaItem->getQtyRequested());
            }
        }
        return $subtotal;
    }

    /**
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
                $bundleChildren = $this->getBundleChildren($orderItem->getSku());
                $bundlePriceType = $bundleProduct->getPriceType();
                foreach ($bundleChildren as $bundleChild) {
                    $bundleChildFromOrder = $this->getBundleChildFromOrder($rmaItem->getOrderItemId(), $bundleChild->getSku());
                    $bundleChildDiscountAmount = $this->getDiscountAmountForBundleChild($bundlePriceType, $orderItem, $bundleChild);
                    $discountAmount += ($bundleChildDiscountAmount * $rmaItem->getQtyRequested() * $bundleChild->getQty() / $this->getNetQty($bundleChildFromOrder));
                }
            } else {
                $discountAmount += ($orderItem->getDiscountAmount() * $rmaItem->getQtyRequested() / $this->getNetQty($orderItem));
            }
        }
        return $discountAmount;
    }

    public function getDiscountAmountForBundleChild($bundlePriceType, $orderItem, $bundleChild)
    {
        $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
            $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getDiscountAmount()) :
            $this->getBundleChildFromOrder($orderItem->getItemId(), $bundleChild->getSku())->getDiscountAmount();

        return $bundleChildDiscountAmount;
    }

    public function getBundleChildFromOrder($itemId, $bundleChildSku)
    {
        $bundleChild = null;
        /** @var \Magento\Sales\Model\Order\Item $itemOrdered */
        $itemOrdered = $this->orderItemRepository->get($itemId);
        $childrenItems = $itemOrdered->getChildrenItems();
        /** @var \Magento\Sales\Model\Order\Item $childItem */
        foreach ($childrenItems as $childItem) {
            if ($childItem->getSku() == $bundleChildSku) {
                $bundleChild = $childItem;
                break;
            }
        }
        return $bundleChild;
    }

    /**
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
                $bundleChildren = $this->getBundleChildren($orderItem->getSku());
                $bundlePriceType = $bundleProduct->getPriceType();

                foreach ($bundleChildren as $bundleChild) {
                    $itemId = $rmaItem->getOrderItemId();
                    $bundleChildFromOrder = $this->getBundleChildFromOrder($itemId, $bundleChild->getSku());
                    $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
                        $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getDiscountAmount()) :
                        $this->getBundleChildFromOrder($itemId, $bundleChild->getSku())->getDiscountAmount();
                    $itemGrandTotalInclTax = $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getRowTotalInclTax())
                        - $bundleChildDiscountAmount;

                    $grandTotal += $this->getRateAmount($itemGrandTotalInclTax, $this->getNetQty($bundleChildFromOrder), $rmaItem->getQtyRequested() * $bundleChild->getQty());
                }
            } else {
                $itemGrandTotal = $orderItem->getRowTotal()
                    - $orderItem->getDiscountAmount();

                $itemGrandTotal = $this->getRateAmount($itemGrandTotal, $this->getNetQty($orderItem), $rmaItem->getQtyRequested());
                $grandTotal += $this->getRateAmount($itemGrandTotal, $this->getNetQty($orderItem), $rmaItem->getQtyRequested());
            }
        }
        return $grandTotal;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @return float|null
     * @throws NoSuchEntityException
     * @throws \Exception
     */
    public function getSumOfChildrenOriginPrice(Item $orderItem)
    {
        $originalPriceSum = 0;

        $childrenItems = $this->getBundleChildren($orderItem->getSku());

        /** @var \Magento\Bundle\Api\Data\LinkInterface $childItem */
        foreach ($childrenItems as $childItem) {
            $originalProductPrice = $this->productRepository->get($childItem->getSku(), false, $orderItem->getStoreId())->getPrice();
            $originalPriceSum += ($originalProductPrice * $childItem->getQty());
        }
        return $originalPriceSum;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @param \Magento\Bundle\Api\Data\LinkInterface $bundleChild
     * @param float $valueToCalculate
     * @return float|int
     * @throws NoSuchEntityException
     */
    public function getProportionOfBundleChild($orderItem, $bundleChild, $valueToCalculate)
    {
        $originalPriceSum = $this->getSumOfChildrenOriginPrice($orderItem);

        $bundleChildPrice = $this->productRepository->get($bundleChild->getSku(), false, $orderItem->getStoreId())->getPrice();
        $rate = ($bundleChildPrice / $originalPriceSum) * $bundleChild->getQty();

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

    public function getBundleChildren($bundleDynamicSku)
    {
        $bundleSku = explode("-", $bundleDynamicSku);
        try {
            return $this->productLinkManagement->getChildren($bundleSku[0]);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
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

    public function updatePosSendCheck($rmaId)
    {
        $tableName = $this->resourceConnection->getTableName('magento_rma');
        $connection = $this->resourceConnection->getConnection();
        $connection->update($tableName, ['pos_rma_send_check' => 1], ['entity_id = ?' => $rmaId]);
    }
}
