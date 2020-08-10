<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Eguana\CustomCheckout\Plugin\Checkout\Block;


/**
 * Shopping cart block
 */
class Cart
{

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    protected $promoItemHelper;

    public function __construct(\Amasty\Promo\Helper\Item $promoItemHelper)
    {
        $this->promoItemHelper = $promoItemHelper;
    }

    /**
     * Return customer quote items with the information whether it is a gift or not
     *
     * @return array
     */
    public function afterGetItems(\Magento\Checkout\Block\Cart $subject, $items)
    {
       $itemsWithGiftInfo = array();
        foreach ($items as $item) {
            if ($this->promoItemHelper->isPromoItem($item)) {
                $item->setData('is_gift', true);
            } else {
                $item->setData('is_gift', false);
            }
            $itemsWithGiftInfo[] = $item;
        }
        return $itemsWithGiftInfo;
    }

}
