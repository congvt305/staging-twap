<?php

namespace CJ\PointRedemption\Plugin;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;

class AttributesQuoteItemToOrderItem
{
    public function aroundConvert(
        ToOrderItem  $subject,
        \Closure     $proceed,
        AbstractItem $item,
        $additional = []
    ) {
        /** @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem = $proceed($item, $additional);
        $orderItem->setIsPointRedeemable($item->getIsPointRedeemable());
        $orderItem->setPointRedemptionAmount($item->getPointRedemptionAmount());
        return $orderItem;
    }
}
