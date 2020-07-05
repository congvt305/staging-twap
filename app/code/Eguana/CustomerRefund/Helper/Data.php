<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/5/20
 * Time: 8:08 AM
 */

namespace Eguana\CustomerRefund\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLED = 'eguana_cutomerrefund/general/enabled';

    public function isEnabled() {
        return $this->scopeConfig->getValue(
            $this::XML_PATH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

}
