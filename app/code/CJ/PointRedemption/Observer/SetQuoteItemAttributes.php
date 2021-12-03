<?php

namespace CJ\PointRedemption\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SetQuoteItemAttributes implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $quoteItem = $observer->getQuoteItem();
        $product = $observer->getProduct();
        $quoteItem->setIsPointRedeemable($product->getIsPointRedeemable());
        $quoteItem->setPointRedemptionAmount($product->getPointRedemptionAmount());
    }
}
