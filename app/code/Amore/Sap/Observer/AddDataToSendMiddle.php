<?php
declare(strict_types=1);

namespace Amore\Sap\Observer;

use CJ\Middleware\Model\Product\Bundle\CalculatePrice;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;

class AddDataToSendMiddle implements ObserverInterface
{
    /**
     * @var CalculatePrice
     */
    private $bundleCalculatePrice;

    /**
     * @var \CJ\Middleware\Model\Product\CalculatePrice
     */
    private $productCalculatePrice;

    /**
     * @var \CJ\Middleware\Model\Data
     */
    private $orderData;

    /**
     * @var \CJ\Middleware\Helper\Data
     */
    private $middlewareHelper;

    /**
     * @var \Amasty\Rewards\Model\Config
     */
    private $amConfig;

    /**
     * @var \CJ\Rewards\Model\Data
     */
    private $rewardData;

    /**
     * @param \CJ\Rewards\Model\Data $rewardData
     * @param CalculatePrice $bundleCalculatePrice
     * @param \CJ\Middleware\Model\Product\CalculatePrice $productCalculatePrice
     * @param \CJ\Middleware\Model\Data $orderData
     * @param \CJ\Middleware\Helper\Data $middlewareHelper
     * @param \Amasty\Rewards\Model\Config $amConfig
     */
    public function __construct(
        \CJ\Rewards\Model\Data $rewardData,
        CalculatePrice $bundleCalculatePrice,
        \CJ\Middleware\Model\Product\CalculatePrice $productCalculatePrice,
        \CJ\Middleware\Model\Data $orderData,
        \CJ\Middleware\Helper\Data $middlewareHelper,
        \Amasty\Rewards\Model\Config $amConfig
    ) {
        $this->bundleCalculatePrice = $bundleCalculatePrice;
        $this->productCalculatePrice = $productCalculatePrice;
        $this->orderData = $orderData;
        $this->middlewareHelper = $middlewareHelper;
        $this->amConfig = $amConfig;
        $this->rewardData = $rewardData;
    }

    /**
     * Calculate price to send SAP
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getData('order');
        $orderItems = $order->getAllItems();
        $storeId = $order->getStoreId();
        $isDecimalFormat = $this->middlewareHelper->getIsDecimalFormat('store', $storeId);
        $shippingAmountPerItem = 0;
        if ($this->middlewareHelper->getIsIncludeShippingAmountWhenSendRequest($storeId)) {
            $shippingAmountPerItem = $this->getShippingAmountPerItem($order);
        }
        $spendingRate = $this->amConfig->getPointsRate($storeId);
        if (!$spendingRate) {
            $spendingRate = 1;
        }
        if($isEnableRewardsPoint = $this->amConfig->isEnabled($storeId)) {
            $rewardPoints = 0;
            if ($order->getData('am_spent_reward_points')) {
                $rewardPoints = $this->orderData->roundingPrice($order->getData('am_spent_reward_points'), $isDecimalFormat);
            }
            if ($this->rewardData->isEnableShowListOptionRewardPoint($storeId)) {
                $listOptions = $this->rewardData->getListOptionRewardPoint($storeId);
                if ($rewardPoints) {
                    $mileageUsedAmount = $listOptions[$rewardPoints] ?? 0;
                    $spendingRate = $rewardPoints / $mileageUsedAmount;
                }
            }
        }
        /** @var Item $orderItem */
        foreach ($orderItems as $orderItem) {
            //Have to check getParentItem because this observer hasn't set parentItemId yet so can not use allVisibileItem to get
            if ($orderItem->getProductType() != 'bundle' && !$orderItem->getParentItem()) {
                $orderItem = $this->productCalculatePrice->calculate($orderItem, $spendingRate, $isEnableRewardsPoint, $isDecimalFormat);
                if ($orderItem->getIsFreeGift()) {
                    $shippingAmount = 0;
                } else {
                    $shippingAmount = $this->orderData->roundingPrice($shippingAmountPerItem * $orderItem->getQtyOrdered(), $isDecimalFormat);
                }
                $orderItem->setData('sap_item_nsamt', $orderItem->getData('sap_item_nsamt') + $shippingAmount);
                $orderItem->setData('sap_item_slamt', $orderItem->getData('sap_item_slamt') + $shippingAmount);
                $orderItem->setData('sap_item_netwr', $orderItem->getData('sap_item_netwr') + $shippingAmount);
            } else {
                if (!$orderItem->getParentItem()) {
                    $orderItem = $this->bundleCalculatePrice->calculate($orderItem, $spendingRate, $isEnableRewardsPoint, $isDecimalFormat);
                    foreach ($orderItem->getChildrenItems() as $bundleChild) {
                        if ($bundleChild->getIsFreeGift()) {
                            $shippingAmountPerChild = 0;
                        } else {
                            $shippingAmountPerChild = $this->orderData->roundingPrice($shippingAmountPerItem * $bundleChild->getQtyOrdered(), $isDecimalFormat);
                        }
                        $itemDcamt = $bundleChild->getData('sap_item_dcamt');
                        $itemNsamt = $bundleChild->getData('sap_item_nsamt') + $shippingAmountPerChild;
                        $itemSlamt = $itemNsamt - $itemDcamt;
                        $itemMiamt = $bundleChild->getData('sap_item_miamt');
                        $itemTaxAmount = $bundleChild->getData('sap_item_mwsbp');
                        $bundleChild->setData('sap_item_slamt', $itemSlamt);
                        $bundleChild->setData('sap_item_netwr', ($itemSlamt - $itemMiamt - $itemTaxAmount));
                        $bundleChild->setData('sap_item_nsamt', $itemNsamt);
                    }
                }
            }
        }
    }


    /**
     * Get shipping amount per item
     *
     * @param Order $order
     * @return float|int
     */
    private function getShippingAmountPerItem($order)
    {
        $orderItems = $order->getAllItems();
        $countItem = 0;
        foreach($orderItems as $orderItem) {
            if ($orderItem->getProductType() != 'bundle' && !$orderItem->getParentItem()) {
                if ($orderItem->getIsFreeGift()) {
                    continue;
                }
                $countItem += $orderItem->getQtyOrdered();
            } else {
                if (!$orderItem->getParentItem()) {
                    foreach ($orderItem->getChildrenItems() as $bundleChild) {
                        if ($bundleChild->getIsFreeGift()) {
                            continue;
                        }
                        $countItem += $bundleChild->getQtyOrdered();
                    }
                }
            }
        }

        //Cover for case all order item is free gift
        $shippingAmount = 0;
        if ($countItem > 0) {
            $shippingAmount = $order->getShippingAmount() / $countItem;
        }
        return $shippingAmount;
    }
}
