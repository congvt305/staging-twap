<?php

namespace Amore\Sap\Model\SapOrder\Product;

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

        if ($isEnableRewardsPoint) {
            $rewardPoint = $orderItem->getData('am_spent_reward_points');
            $mileageAmountItem = $this->orderData->roundingPrice($rewardPoint / $spendingRate);
        } else {
            $mileageAmountItem = 0;
        }
        $itemSubtotal = $this->orderData->roundingPrice($orderItem->getPrice() * $orderItem->getQtyOrdered(), $isDecimalFormat);
        $itemTotalDiscount = $this->orderData->roundingPrice($orderItem->getDiscountAmount(), $isDecimalFormat) - $mileageAmountItem;
        $itemSaleAmount = $itemSubtotal - $itemTotalDiscount;
        $itemTaxAmount = $this->orderData->roundingPrice($orderItem->getTaxAmount(), $isDecimalFormat);

        $itemNetwr = $itemSubtotal - $itemTotalDiscount - $this->orderData->roundingPrice($mileageAmountItem, $isDecimalFormat) - $itemTaxAmount;
        $orderItem->setData('mileage_amount', $mileageAmountItem);
        $orderItem->setData('normal_sales_amount', $itemSubtotal);
        $orderItem->setData('discount_amount', $itemTotalDiscount);
        $orderItem->setData('sales_amount', $itemSaleAmount);
        $orderItem->setData('net_amount', $itemNetwr);
        return $orderItem;
    }
}
