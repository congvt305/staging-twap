<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/17/20
 * Time: 10:50 AM
 */

namespace Eguana\GWLogistics\Plugin\Rma;

use Magento\Rma\Api\Data\TrackInterface;

class TrackAttributeLoad
{
    /**
     * @param \Magento\Rma\Api\Data\TrackInterface $subject
     * @param $result
     */
    public function afterGetExtensionAttributes(\Magento\Rma\Api\Data\TrackInterface $subject, $result)
    {
        // TODO: Implement plugin method.
        return $result;
    }

}
