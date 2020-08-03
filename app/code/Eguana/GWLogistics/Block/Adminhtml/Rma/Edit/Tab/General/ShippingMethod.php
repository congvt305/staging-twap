<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/14/20
 * Time: 6:27 AM
 */

namespace Eguana\GWLogistics\Block\Adminhtml\Rma\Edit\Tab\General;


class ShippingMethod extends \Magento\Rma\Block\Adminhtml\Rma\Edit\Tab\General\Shippingmethod
{
    public function canShowReverseLogisticsOrderGenerationButton()
    {
       return  ($this->getShipment()->getTrackNumber() === NULL);
    }

    /**
     * @param \Magento\Rma\Api\Data\RmaInterface $rma
     * @return string
     */
    public function getReverseOrderUrl($rma)
    {
        return $this->getUrl('eguana_gwlogistics/reverseorder/create', ['rma_id' => $rma->getEntityId()]);
    }

}
