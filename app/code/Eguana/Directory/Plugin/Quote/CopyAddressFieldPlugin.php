<?php

namespace Eguana\Directory\Plugin\Quote;


use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\ToOrderAddress;

class CopyAddressFieldPlugin
{

    /**
     * @param \Magento\Quote\Model\Quote\Address\ToOrderAddress $subject
     * @param $result
     * @param Address $object
     * @param array $data
     */
    public function afterConvert(\Magento\Quote\Model\Quote\Address\ToOrderAddress $subject, $result, Address $object, $data = [])
    {
        if ($object->getCityId()) {
            $result->setCityId($object->getCityId());
        }
        if ($object->getWardId()) {
            $result->setWardId($object->getWardId());
        }
        if ($object->getWard()) {
            $result->setWard($object->getWard());
        }
        return $result;
    }
}
