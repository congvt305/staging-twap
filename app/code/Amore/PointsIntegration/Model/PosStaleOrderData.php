<?php

namespace Amore\PointsIntegration\Model;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;

class PosStaleOrderData extends PosOrderData
{
    protected $qtyRatio = 1;

    protected $products = [];

    public function getItemData(Order $order)
    {
        $orderItemData = [];
        $itemsSubtotal = 0;
        $itemsDiscountAmount = 0;
        $itemsGrandTotal = 0;

        $orderItems = $order->getAllVisibleItems();

        /** @var Item $orderItem */
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() != 'bundle') {

                $itemSubtotal = $this->simpleAndConfigurableSubtotal($orderItem);
                $itemTotalDiscount = $this->simpleAndConfigurableTotalDiscount($orderItem);
                $itemGrandTotal = $itemSubtotal - $itemTotalDiscount;

                $orderItemData[] = [
                    'prdCD' => $orderItem->getSku(),
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
                $bundleChildren = $this->getBundleChildren($orderItem);
                $bundlePriceType = $bundleProduct->getPriceType();

                foreach ($bundleChildren as $bundleChild) {
                    $itemId = $orderItem->getItemId();
                    $bundleChildFromOrder = $this->getBundleChildFromOrder($itemId, $bundleChild->getId());
                    $this->qtyRatio = $bundleChildFromOrder->getQtyOrdered() / $orderItem->getQtyOrdered();
                    $bundleChildPrice = $bundleChild->getPrice();
                    $bundleChildSubtotal = $this->bundleChildSubtotal($bundleChildPrice, $bundleChildFromOrder);
                    $bundleChildDiscountAmount = $this->bundleChildDiscountAmount($bundlePriceType, $orderItem, $bundleChild);
                    $priceGap = $orderItem->getOriginalPrice() - $orderItem->getPrice();
                    $childPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getOriginalPrice()) / $this->qtyRatio;
                    $catalogRuledPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, ($priceGap)) / $this->qtyRatio;

                    $itemTotalDiscount = abs(round(
                        $bundleChildDiscountAmount +
                        (($bundleChildPrice - $childPriceRatio) * $bundleChildFromOrder->getQtyOrdered()) +
                        $catalogRuledPriceRatio * $bundleChildFromOrder->getQtyOrdered()
                    ));

                    $bundleChildGrandTotal = $bundleChildSubtotal - $itemTotalDiscount;

                    $orderItemData[] = [
                        'prdCD' => $bundleChild->getSku(),
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
            }
        }

        $orderSubtotal = $this->getOrderSubtotal($order);
        $orderGrandTotal = $this->getOrderGrandTotal($order);
        $orderDiscount = $orderSubtotal - $orderGrandTotal;

        $orderItemData = $this->priceCorrector($orderSubtotal, $itemsSubtotal, $orderItemData, 'salAmt');
        $orderItemData = $this->priceCorrector($orderDiscount, $itemsDiscountAmount, $orderItemData, 'dcAmt');
        $orderItemData = $this->priceCorrector($orderGrandTotal, $itemsGrandTotal, $orderItemData, 'netSalAmt');

        return $orderItemData;
    }

    public function getBundleChildrenTotalAmount(Item $orderItem)
    {
        $originalPriceTotal = 0;
        $childrenItems = $this->getBundleChildren($orderItem);

        /** @var LinkInterface $childItem */
        foreach ($childrenItems as $childItem) {
            $originalProductPrice = $this->productRepository->get($childItem->getSku(), false, $orderItem->getStoreId())->getPrice();
            $originalPriceTotal += ($originalProductPrice * $this->qtyRatio);
        }
        return $originalPriceTotal;
    }

    public function getBundleExtraAmount($order)
    {
        $orderItems = $order->getAllVisibleItems();
        $priceDifferences = 0;

        /** @var Item $orderItem */
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() == 'bundle') {
                /** @var \Magento\Catalog\Model\Product $bundleProduct */
                $bundleProduct = $this->productRepository->getById($orderItem->getProductId(), false, $order->getStoreId());
                $bundleChildren = $this->getBundleChildren($orderItem);
                $bundlePriceType = $bundleProduct->getPriceType();

                if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    foreach ($bundleChildren as $bundleChild) {
                        $childPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getOriginalPrice()) / $this->qtyRatio;
                        $originItemPrice = $this->productRepository->get($bundleChild->getSku(), false, $order->getStoreId())->getPrice();
                        $bundleChildByOrder = $this->getBundleChildFromOrder($orderItem->getItemId(), $bundleChild->getId());

                        $priceDifferences += (($originItemPrice - $childPriceRatio) * $bundleChildByOrder->getQtyOrdered());
                    }
                }
            }
        }
        return $priceDifferences;
    }

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
                $bundleChildren = $this->getBundleChildren($orderItem);
                $bundlePriceType = $bundleProduct->getPriceType();

                if ((int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
                    foreach ($bundleChildren as $bundleChild) {
                        $bundleChildByOrder = $this->getBundleChildFromOrder($orderItem->getItemId(), $bundleChild->getId());
                        $catalogRuledPriceRatio = $this->getProportionOfBundleChild($orderItem, $bundleChild, ($orderItem->getOriginalPrice() - $orderItem->getPrice())) / $this->qtyRatio;

                        $catalogRuleDiscount += $catalogRuledPriceRatio * $bundleChildByOrder->getQtyOrdered();
                    }
                }
            }
        }
        return $catalogRuleDiscount;
    }

    public function bundleChildDiscountAmount($bundlePriceType, $orderItem, $bundleChild)
    {
        $bundleChildDiscountAmount = (int)$bundlePriceType !== \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC ?
            round($this->getProportionOfBundleChild($orderItem, $bundleChild, $orderItem->getDiscountAmount())) :
            round($this->getBundleChildFromOrder($orderItem->getItemId(), $bundleChild->getId())->getDiscountAmount());

        return $bundleChildDiscountAmount;
    }

    /**
     * @param $orderItem
     * @return array|LinkInterface[]|void
     * @throws NoSuchEntityException
     */
    public function getBundleChildren($orderItem)
    {
        $children = [];
        $productId = $orderItem->getProductId();
        if (!isset($this->products[$productId])) {
            $childrenSku = explode("-", $orderItem->getSku());
            unset($childrenSku[0]);
            foreach ($childrenSku as $sku) {
                $children[] = $this->productRepository->get($sku);
            }
            $this->products[$productId] = $children;
        }

        return $this->products[$productId];
    }

    public function getBundleChildFromOrder($itemId, $bundleChildId)
    {
        $bundleChild = null;
        /** @var Item $itemOrdered */
        $itemOrdered = $this->orderItemRepository->get($itemId);

        $childrenItems = $itemOrdered->getChildrenItems();
        /** @var Item $childItem */
        foreach ($childrenItems as $childItem) {
            if ($childItem->getProductId() == $bundleChildId) {
                $bundleChild = $childItem;
                break;
            }
        }
        return $bundleChild;
    }

    public function getProportionOfBundleChild($orderItem, $bundleChild, $valueToCalculate)
    {
        $bundleChildrenTotalAmount = $this->getBundleChildrenTotalAmount($orderItem);

        $bundleChildPrice = $this->productRepository->get($bundleChild->getSku(), false, $orderItem->getStoreId())->getPrice();
        $rate = ($bundleChildPrice / $bundleChildrenTotalAmount) * $this->qtyRatio;

        return $valueToCalculate * $rate;
    }
}
