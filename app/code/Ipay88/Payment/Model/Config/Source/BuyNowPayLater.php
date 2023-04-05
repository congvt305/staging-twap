<?php

namespace Ipay88\Payment\Model\Config\Source;

use Ipay88\Payment\Gateway\Config\Config;

class BuyNowPayLater implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return array_map(function ($type) {
            return [
                'value' => $type['id'],
                'label' => __($type['name']),
            ];
        }, Config::PAYMENT_TYPES['BUY_NOW_PAY_LATER']);
    }
}