<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/8/20
 * Time: 8:04 AM
 */

namespace Eguana\GWLogistics\Plugin;


use Magento\Sales\Model\Order;

class LoadCvsLocationForOrder
{

    /**
     * @param \Magento\Sales\Model\Order $subject
     * @param \Magento\Sales\Model\Order\Address|null $result
     */
    public function afterGetShippingAddress(\Magento\Sales\Model\Order $subject, $result)
    {
//        if($result->getExtensionAttributes() == null)
//        {
//            $result->set
//        }
        return $result;
    }
}
