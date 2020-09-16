<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 9/16/20
 * Time: 8:14 AM
 */

namespace Eguana\Directory\Plugin;


use Magento\Checkout\CustomerData\Cart;

class CartPlugin
{

    /**
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param $result
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        if (isset($result['subtotal_incl_tax'], $result['subtotal_excl_tax']) && ($result['subtotalAmount'] === null || $result['subtotalAmount'] === "0.0000")) {
            $result['subtotal_incl_tax'] = "0.0000";
            $result['subtotal_excl_tax'] = "0.0000";
        }
        return $result;
    }
}