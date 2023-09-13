<?php
declare(strict_types=1);

namespace CJ\Middleware\Model\Product\Bundle;

class CalculatePrice
{
    /**
     * @var \CJ\Middleware\Model\Data
     */
    private $orderData;

    /**
     * @param \CJ\Middleware\Model\Data $orderData
     */
    public function __construct(
        \CJ\Middleware\Model\Data $orderData
    ) {
        $this->orderData = $orderData;
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
                if ($bundleItem->getIsFreeGift()) {
                    continue;
                }
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
            $parentProductPrice = $orderItem->getPrice() * $orderItem->getQtyOrdered();
            foreach ($bundleItems as $bundleItem) {
                if (!$bundleItem->getPrice()) {
                    if ($bundleItem->getIsFreeGift()) {
                        $bundleItem->setData('sap_item_nsamt', 0);
                        $bundleItem->setData('sap_item_reward_point', 0);
                        $bundleItem->setData('sap_item_dcamt', 0);
                        $bundleItem->setData('sap_item_miamt', 0);
                        $bundleItem->setData('sap_item_mwsbp', 0);
                    } else {
                        $priceRatio = $bundleItem->getQtyOrdered() / $totalItemsQtyOrdered;
                        $bundlItemPrice = $this->orderData->roundingPrice($parentProductPrice * $priceRatio, $isDecimalFormat);
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

                        $orderItem->setData('sap_item_miamt', $mileageAmountItem);
                        $orderItem->setData('sap_item_nsamt', $bundlItemPrice);
                        $orderItem->setData('sap_item_dcamt', $bundleItemDiscountAmount);
                        $orderItem->setData('sap_item_reward_point', $this->orderData->roundingPrice($rewardPointItem ?? 0, $isDecimalFormat));
                        $orderItem->setData('sap_item_mwsbp', $itemTaxAmount);
                    }
                }
            }

            //Correct price
            foreach ($bundleItems as $bundleItem) {
                if ($bundleItem->getIsFreeGift()) {
                    continue;
                }
                if ($parentProductPrice != $totalPrice) {
                    $gapAmount = $parentProductPrice - $totalPrice;
                    $bundleItem->setData('sap_item_nsamt', $bundleItem->getData('normal_sales_amount') + $gapAmount);
                }
                if ($rewardPoint != $totalRewardPoint) {
                    $gapRewardPointAmount = $rewardPoint - $totalRewardPoint;
                    $bundleItem->setData('sap_item_reward_point', $bundleItem->getData('am_spent_reward_points') + $gapRewardPointAmount);
                }
                if ($totalDiscountAmount > 0) {
                    $bundleItem->setData('sap_item_dcamt', $bundleItem->getData('discount_amount') + $totalDiscountAmount);
                }
                if ($totalMileageAmount > 0) {
                    $bundleItem->setData('sap_item_miamt', $bundleItem->getData('mileage_amount') + $totalMileageAmount);
                }
                if ($totalTaxAmount > 0) {
                    $bundleItem->setData('sap_item_mwsbp', $bundleItem->getData('tax_amount') + $totalTaxAmount);
                }
                break;
            }
        } else {
            foreach ($bundleItems as $bundleItem) {
                if ($isEnableRewardsPoint) {
                    $rewardPointItem = $bundleItem->getData('am_spent_reward_points');
                    $mileageAmountItem = $bundleItem->getData('am_spent_reward_points') / $spendingRate;
                } else {
                    $rewardPointItem = 0;
                    $mileageAmountItem = 0;
                }
                $itemSlamt = $this->orderData->roundingPrice($bundleItem->getPrice() * $bundleItem->getQtyOrdered(), $isDecimalFormat);
                $bundleItemDiscountAmount = $bundleItem->getDiscountAmount() - $mileageAmountItem;
                $bundleItem->setData('sap_item_dcamt', $bundleItemDiscountAmount);
                $bundleItem->setData('sap_item_miamt', $mileageAmountItem);
                $bundleItem->setData('sap_item_nsamt', $itemSlamt);
                $orderItem->setData('sap_item_mwsbp', $bundleItem->getTaxAmount());
                $orderItem->setData('sap_item_reward_point', $this->orderData->roundingPrice($rewardPointItem, $isDecimalFormat));
            }
        }
        return $orderItem;
    }
}
