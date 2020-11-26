<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/25/20
 * Time: 2:22 PM
 */
declare(strict_types=1);

namespace Eguana\BlackCat\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const XML_PATH_SHIPPING_MESSAGE = 'carriers/blackcat/shipping_message';
    const XML_PATH_ACTIVE = 'carriers/blackcat/active';

    public function getCarrierShippingMessage($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SHIPPING_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isActive($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

}