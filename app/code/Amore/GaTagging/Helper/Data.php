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
    const XML_PATH_IS_ENABLED = 'amore_gatagging/tagmanager/active';
    const XML_PATH_CONTAINER_ID = 'amore_gatagging/tagmanager/container_id';

    public function getSiteName()
    {
            return $this->scopeConfig->getValue(
                self::XML_PATH_SITE_NAME,
                ScopeInterface::SCOPE_STORE
            );
    }
    public function isActive()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_IS_ENABLED,
            ScopeInterface::SCOPE_STORE
        );

    }

    public function getContainerId()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONTAINER_ID,
            ScopeInterface::SCOPE_STORE
        );
    }


}
