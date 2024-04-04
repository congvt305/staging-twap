<?php

namespace CJ\PointRedemption\Observer;

use CJ\PointRedemption\Setup\Patch\Data\AddRedemptionAttributes;
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
        $isRedeemableProduct = $product->getData(AddRedemptionAttributes::IS_POINT_REDEEMABLE_ATTRIBUTE_CODE);
        $isPointRedemptionOption = $quoteItem->getOptionByCode('is_point_redemption');
        $isPointRedemption = $isPointRedemptionOption && $isPointRedemptionOption->getValue();
        if ($isRedeemableProduct && $isPointRedemption) {
            $quoteItem->setIsPointRedeemable($product->getIsPointRedeemable());
            $quoteItem->setPointRedemptionAmount($product->getPointRedemptionAmount());
        }
    }
}
