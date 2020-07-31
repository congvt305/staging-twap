<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/31/20
 * Time: 8:37 AM
 */

namespace Eguana\GWLogistics\Plugin\Rma;


use Magento\Rma\Model\Shipping;

class ShippingPlugin
{

    /**
     * @param \Magento\Rma\Model\Shipping $subject
     */
    public function beforeGetNumberDetail(\Magento\Rma\Model\Shipping $subject)
    {
        $trackNumber = $subject->getTrackNumber();
        $subject->setTrackNumber('R|'.$trackNumber);
        return;
    }
}
