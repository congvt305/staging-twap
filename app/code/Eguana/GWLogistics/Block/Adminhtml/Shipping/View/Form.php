<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 8/3/20
 * Time: 8:11 AM
 */

namespace Eguana\GWLogistics\Block\Adminhtml\Shipping\View;


class Form extends \Magento\Shipping\Block\Adminhtml\View\Form
{
    public function canShowGWButton()
    {
        return  ($this->getOrder()->getShippingMethod() === 'gwlogistics_CVS') && ($this->getShipment()->getData('all_pay_logistics_id') === NULL);
    }

    public function getGwShipmentOrderUrl()
    {
        return $this->getUrl('eguana_gwlogistics/shipmentorder/create', ['shipment_id' => $this->getShipment()->getEntityId()]);
    }

}
