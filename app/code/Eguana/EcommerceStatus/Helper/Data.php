<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/12/20
 * Time: 6:31 AM
 */

namespace Eguana\EcommerceStatus\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
 *
 * Class Data
 */
class Data extends AbstractHelper
{
    /**
     * Get E-commerce Status
     * @return bool
     */
    public function getECommerceStatus()
    {
        return (bool) $this->scopeConfig->getValue(
            'ecommerce_status/general/ecommerce_enabled',
            ScopeInterface::SCOPE_STORE
        );
    }
}
