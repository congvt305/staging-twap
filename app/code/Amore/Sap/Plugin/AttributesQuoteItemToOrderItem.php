<?php
declare(strict_types=1);

namespace Amore\Sap\Plugin;

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
        $orderItem->setIsFreeGift($item->getIsFreeGift());
        return $orderItem;
    }
}
