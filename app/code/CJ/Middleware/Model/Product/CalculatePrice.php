<?php
declare(strict_types=1);

namespace CJ\Middleware\Model\Product;

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

        if ($isEnableRewardsPoint) {
            $rewardPoint = $orderItem->getData('am_spent_reward_points');
            $mileageAmountItem = $this->orderData->roundingPrice($rewardPoint / $spendingRate);
        } else {
            $rewardPoint = 0;
            $mileageAmountItem = 0;
        }
        $itemSubtotal = $this->orderData->roundingPrice($orderItem->getPrice() * $orderItem->getQtyOrdered(), $isDecimalFormat);
        $itemTotalDiscount = $this->orderData->roundingPrice($orderItem->getDiscountAmount(), $isDecimalFormat) - $mileageAmountItem;
        $itemSaleAmount = $itemSubtotal - $itemTotalDiscount;
        $itemTaxAmount = $this->orderData->roundingPrice($orderItem->getTaxAmount(), $isDecimalFormat);

        $itemNetwr = $itemSubtotal - $itemTotalDiscount - $this->orderData->roundingPrice($mileageAmountItem, $isDecimalFormat) - $itemTaxAmount;

        if ($itemNetwr < 0) {
            $mileageAmountItem = $itemSubtotal - $itemTotalDiscount - $itemTaxAmount;
            $rewardPoint = $mileageAmountItem * $spendingRate;
        }

        $orderItem->setData('sap_item_miamt', $mileageAmountItem);
        $orderItem->setData('sap_item_nsamt', $itemSubtotal);
        $orderItem->setData('sap_item_dcamt', $itemTotalDiscount);
        $orderItem->setData('sap_item_slamt', $itemSaleAmount);
        $orderItem->setData('sap_item_netwr', $itemNetwr);
        $orderItem->setData('sap_item_reward_point', $this->orderData->roundingPrice($rewardPoint ?? 0, $isDecimalFormat));
        $orderItem->setData('sap_item_mwsbp', $itemTaxAmount);
        return $orderItem;
    }
}
