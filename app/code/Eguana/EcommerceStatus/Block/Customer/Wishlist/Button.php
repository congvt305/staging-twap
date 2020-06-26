<?php

namespace Eguana\EcommerceStatus\Block\Customer\Wishlist;

use Eguana\EcommerceStatus\Helper\Data;
use Magento\Wishlist\Block\Customer\Wishlist\Button as CartButton;

/**
 *
 * Class Button
 * @package Eguana\EcommerceStatus\Block\Customer\Wishlist
 */
class Button extends CartButton
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * RemoveAddToButton constructor.
     * @param Data $helperData
     */
    public function __construct(
       \Magento\Framework\View\Element\Template\Context $context,
       \Magento\Wishlist\Helper\Data $wishlistData,
       \Magento\Wishlist\Model\Config $wishlistConfig,
        Data $helperData
    ) {
        $this->helperData = $helperData;
        parent::__construct($context, $wishlistData, $wishlistConfig);
    }
    /**
     * @return bool
     */
    public function canAddToCart()
    {
        if (!$this->helperData->getECommerceStatus()) {
            return false;
        }
        return true;
    }
}
