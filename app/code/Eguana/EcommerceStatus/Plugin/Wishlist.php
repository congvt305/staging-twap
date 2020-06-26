<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: saba
 * Date: 6/25/20
 * Time: 1:31 PM
 */
namespace Eguana\EcommerceStatus\Plugin;

use Eguana\EcommerceStatus\Helper\Data;
use Magento\Wishlist\Model\Wishlist as WishlistModel;

/**
 *Hide wishlist Add all to cart button
 *
 * Class Wishlist
 * @package Eguana\EcommerceStatus\Plugin
 */
class Wishlist
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * Wishlist constructor.
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Hide wishlist Add all to cart button
     * @param WishlistModel $wishlist
     * @param $result
     * @return bool
     */
    public function afterIsSalable(WishlistModel $wishlist, $result)
    {
        if (!$this->helperData->getECommerceStatus()) {
            return false;
        }
        return $result;
    }
}

