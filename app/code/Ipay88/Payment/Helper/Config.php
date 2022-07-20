<?php

namespace Ipay88\Payment\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_TESTING = 'ipay88_payment/testing';

    /**
     * Check is testing mode
     *
     * @return bool
     */
    public function isTestingMode()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_TESTING);
    }
}