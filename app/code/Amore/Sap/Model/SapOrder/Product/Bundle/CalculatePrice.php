<?php

namespace Amore\Sap\Model\SapOrder\Product\Bundle;

use Amore\Sap\Model\SapOrder\Data;

class CalculatePrice
{
    /**
     * @var Data
     */
    private $orderData;

    /**
     * @var \Amasty\Rewards\Model\Config
     */
    private $amConfig;

    /**
     * @param \CJ\Middleware\Model\Data $orderData
     * @param \Amasty\Rewards\Model\Config $amConfig
     */
    public function __construct(
        \CJ\Middleware\Model\Data $orderData,
        \Amasty\Rewards\Model\Config $amConfig
    ) {
        $this->orderData = $orderData;
        $this->amConfig = $amConfig;
    }

    /**
     * @param $orderItem
     * @param $spendingRate
     * @return mixed|void
     */
    public function calculate($orderItem, $spendingRate, $isEnableRewardsPoint, $isDecimalFormat) {
        $productPriceType = $orderItem->getProduct()->getPriceType();
        $bundleItems = $orderItem->getChildrenItems();

        if ($productPriceType != \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            $rewardPoint = $orderItem->getData('am_spent_reward_points');
            $totalItemsQtyOrdered = 0;
            /** @var \Magento\Sales\Model\Order\Item $bundleItem */
            foreach ($bundleItems as $bundleItem) {
                $totalItemsQtyOrdered += $bundleItem->getQtyOrdered();
            }
            $totalPrice = 0;
            $totalRewardPoint = 0;
            $totalMileageAmount = 0;
            $totalTaxAmount = $orderItem->getTaxAmount();
            if ($isEnableRewardsPoint) {
                $totalMileageAmount = $rewardPoint / $spendingRate;
            }
            $totalDiscountAmount = $orderItem->getDiscountAmount() - $totalMileageAmount; //discount amount does not include discount from point
            foreach ($bundleItems as $bundleItem) {
                if (!$bundleItem->getPrice()) {
                    $priceRatio = $bundleItem->getQtyOrdered() / $totalItemsQtyOrdered;
                    $bundlItemPrice = $this->orderData->roundingPrice($orderItem->getPrice() * $priceRatio, $isDecimalFormat);
                    $itemTaxAmount = $this->orderData->roundingPrice($orderItem->getTaxAmount() * $priceRatio, $isDecimalFormat);
                    if ($isEnableRewardsPoint) {
                        $rewardPointItem = $rewardPoint * $priceRatio;
                        $mileageAmountItem = $this->orderData->roundingPrice($rewardPointItem / $spendingRate, $isDecimalFormat);
                    } else {
                        $rewardPointItem = 0;
                        $mileageAmountItem = 0;
                    }

                    $bundleItemDiscountAmount = $this->orderData->roundingPrice($orderItem->getDiscountAmount() * $priceRatio, $isDecimalFormat) - $mileageAmountItem;
                    $totalPrice += $bundlItemPrice;
                    $totalRewardPoint += $rewardPointItem;
                    $totalDiscountAmount -= $bundleItemDiscountAmount;
                    $totalMileageAmount -= $mileageAmountItem;
                    $totalTaxAmount -= $itemTaxAmount;

                    $bundleItem->setData('normal_sales_amount', $bundlItemPrice);
                    $bundleItem->setData('am_spent_reward_points', $rewardPointItem);
                    $bundleItem->setData('discount_amount', $bundleItemDiscountAmount);
                    $bundleItem->setData('mileage_amount', $mileageAmountItem);
                    $bundleItem->setData('tax_amount', $itemTaxAmount);
                }
            }

            //Correct price
            foreach ($bundleItems as $bundleItem) {
                if ($orderItem->getPrice() != $totalPrice) {
                    $gapAmount = $orderItem->getPrice() - $totalPrice;
                    $bundleItem->setPrice($bundleItem->getPrice() + $gapAmount);
                }
                if ($rewardPoint != $totalRewardPoint) {
                    $gapRewardPointAmount = $rewardPoint - $totalRewardPoint;
                    $bundleItem->setData('am_spent_reward_points', $bundleItem->getData('am_spent_reward_points') + $gapRewardPointAmount);
                }
                if ($totalDiscountAmount > 0) {
                    $bundleItem->setData('discount_amount', $bundleItem->getData('discount_amount') + $totalDiscountAmount);
                }
                if ($totalMileageAmount > 0) {
                    $bundleItem->setData('mileage_amount', $bundleItem->getData('mileage_amount') + $totalMileageAmount);
                }
                if ($totalTaxAmount > 0) {
                    $bundleItem->setData('tax_amount', $bundleItem->getData('tax_amount') + $totalTaxAmount);
                }
                break;
            }
        } else {
            foreach ($bundleItems as $bundleItem) {
                if ($isEnableRewardsPoint) {
                    $mileageAmountItem = $bundleItem->getData('am_spent_reward_points') / $spendingRate;
                } else {
                    $mileageAmountItem = 0;
                }
                $itemSlamt = $this->orderData->roundingPrice($bundleItem->getPrice() * $bundleItem->getQtyOrdered(), $isDecimalFormat);
                $bundleItemDiscountAmount = $bundleItem->getDiscountAmount() - $mileageAmountItem;
                $bundleItem->setData('discount_amount', $bundleItemDiscountAmount);
                $bundleItem->setData('mileage_amount', $mileageAmountItem);
                $bundleItem->setData('normal_sales_amount', $itemSlamt);
            }
        }
        return $orderItem;
    }
}
