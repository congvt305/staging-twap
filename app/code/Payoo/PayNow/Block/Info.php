<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Payoo\PayNow\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;
use Payoo\PayNow\Gateway\Response\FraudHandler;

class Info extends ConfigurableInfo
{

    protected function getLabel($field)
    {
        return __($field);
    }

    protected function getValueView($field, $value)
    {
        return parent::getValueView($field, $value);
    }
}
