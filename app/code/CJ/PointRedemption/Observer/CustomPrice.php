<?php

namespace CJ\PointRedemption\Observer;

use CJ\PointRedemption\Setup\Patch\Data\AddRedemptionAttributes;
use Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

class CustomPrice implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $item = $observer->getEvent()->getData('quote_item');
        $item = ($item->getParentItem() ? $item->getParentItem() : $item);
        $isRedeemableProduct = $item->getData(AddRedemptionAttributes::IS_POINT_REDEEMABLE_ATTRIBUTE_CODE);
        $isPointRedemptionOption = $item->getOptionByCode('is_point_redemption');
        $isPointRedemption = $isPointRedemptionOption && $isPointRedemptionOption->getValue();
        if ($isRedeemableProduct && $isPointRedemption) {
            $item->setCustomPrice(0);
            $item->setOriginalCustomPrice(0);
            $item->getProduct()->setIsSuperMode(true);
        }
    }
}
