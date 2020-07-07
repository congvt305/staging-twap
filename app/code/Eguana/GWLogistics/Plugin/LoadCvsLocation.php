<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/8/20
 * Time: 8:02 AM
 */

namespace Eguana\GWLogistics\Plugin;


use Magento\Sales\Model\Order;

class LoadCvsLocation
{

    /**
     * @param \Magento\Sales\Model\Order $subject
     */
    public function beforeGetShippingAddress(\Magento\Sales\Model\Order $subject)
    {
        $order = $subject;
        $a = 3;

    }
}
