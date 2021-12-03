<?php

namespace CJ\PointRedemption\Observer;

use Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

class CustomPrice implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $item = $observer->getEvent()->getData('quote_item');
        $item = ( $item->getParentItem() ? $item->getParentItem() : $item );
        $item->setCustomPrice(0);
        $item->setOriginalCustomPrice(0);
        $item->getProduct()->setIsSuperMode(true);
    }
}
