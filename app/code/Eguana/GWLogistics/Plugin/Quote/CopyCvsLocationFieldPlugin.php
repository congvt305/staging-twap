<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/8/20
 * Time: 7:00 AM
 */

namespace Eguana\GWLogistics\Plugin\Quote;


use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\ToOrderAddress;

class CopyCvsLocationFieldPlugin
{

    /**
     * @param \Magento\Quote\Model\Quote\Address\ToOrderAddress $subject
     * @param $result
     * @param Address $object
     * @param array $data
     */
    public function afterConvert(\Magento\Quote\Model\Quote\Address\ToOrderAddress $subject, $result, Address $object, $data = [])
    {
        if ($object->getCvsLocationId()) {
            $result->setCvsLocationId($object->getCvsLocationId());
        }
        return $result;
    }
}
