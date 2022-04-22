<?php

namespace Eguana\Directory\Plugin\Quote;

use Magento\Quote\Model\Quote\Address;

class CopyQuoteAddressToAddressPlugin
{
    /**
     * Set custom attribute to address
     *
     * @param Address $subject
     * @param $result
     * @return mixed
     */
    public function afterExportCustomerAddress(Address $subject, $result)
    {
        if ($subject->getCityId()) {
            $result->setCustomAttribute('city_id', $subject->getCityId());
        }
        if ($subject->getWardId()) {
            $result->setCustomAttribute('ward_id', $subject->getWardId());
        }
        if ($subject->getWard()) {
            $result->setCustomAttribute('ward', $subject->getWard());
        }
        return $result;
    }
}
