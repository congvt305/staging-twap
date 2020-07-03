<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/2/20
 * Time: 7:06 AM
 */

namespace Amore\GaTagging\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_SITE_NAME = 'amore_gatagging/tagmanager/site_name';

    public function getSiteName()
    {
            return $this->scopeConfig->getValue(
                self::XML_PATH_SITE_NAME,
                ScopeInterface::SCOPE_STORE
            );
    }


}
