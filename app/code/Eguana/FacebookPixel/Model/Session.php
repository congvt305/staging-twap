<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 9/2/21
 * Time: 5:04 AM
 */
namespace Eguana\FacebookPixel\Model;

use Magento\Framework\Session\SessionManager;

/**
 * Model class to handle session data
 *
 * Class Session
 */
class Session extends SessionManager
{
    /**
     * Set add to cart
     *
     * @param array $data
     * @return $this
     */
    public function setAddToCart($data)
    {
        $this->setData('add_to_cart', $data);
        return $this;
    }

    /**
     * Get add to cart
     *
     * @return mixed|null
     */
    public function getAddToCart()
    {
        if ($this->hasAddToCart()) {
            $data = $this->getData('add_to_cart');
            $this->unsetData('add_to_cart');
            return $data;
        }
        return null;
    }

    /**
     * Has add to cart
     *
     * @return mixed
     */
    public function hasAddToCart()
    {
        return $this->hasData('add_to_cart');
    }
}
