<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 2/7/21
 * Time: 4:15 PM
 */
namespace Eguana\CustomCheckout\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Checkout\Helper\Cart;

/**
 * This class is used to check if checkout cart is empty or not
 * Class CheckCart
 */
class CheckCart implements ArgumentInterface
{
    /**
     * @var Cart
     */
    private $cartHelper;

    /**
     * CheckCart constructor.
     * @param Cart $cartHelper
     */
    public function __construct(Cart $cartHelper)
    {
        $this->cartHelper = $cartHelper;
    }

    /**
     * This method returns true if the cart is empty
     * @return bool
     */
    public function isCartEmpty()
    {
        if ($this->cartHelper->getItemsCount() === 0) {
            return true;
        }
        return false;
    }
}
