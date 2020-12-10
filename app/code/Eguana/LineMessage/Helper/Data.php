<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 26/11/20
 * Time: 4:42 PM
 */
namespace Eguana\LineMessage\Helper;

use Magento\Framework\App\Helper\AbstractHelper as AbstractHelperAlias;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * Get configuration values
 */
class Data extends AbstractHelperAlias
{
    const XML_LINE_MESSAGE_ENABLE                 = 'LineMessage/general/linemessage_mod_enable';
    const XML_LINE_MESSAGE_LINE_ENABLE            = 'LineMessage/line/line_enable';
    const XML_LINE_MESSAGE_LINE_CHANNEL_ID        = 'LineMessage/line/channel_id';
    const XML_LINE_MESSAGE_LINE_CHANNEL_SECRET    = 'LineMessage/line/channel_secret';
    const XML_LINE_MESSAGE_ACCESS_TOKEN           = 'LineMessage/line/channel_access_token';

    /**
     * Check if module is enabled
     * @param null $store
     * @return bool
     */
    public function isEnabledInFrontend($store = null)
    {
        $isEnabled = true;
        $enabled =  $this->scopeConfig->getValue(self::XML_LINE_MESSAGE_ENABLE, ScopeInterface::SCOPE_STORE);
        if ($enabled == null || $enabled == '0') {
            $isEnabled = false;
        }
        return $isEnabled;
    }

    /**
     * Check if line is enabled
     * @param null $store
     * @return bool
     */
    public function isEnabledLine($store = null)
    {
        $isEnabled = true;
        $enabled =  $this->scopeConfig->getValue(self::XML_LINE_MESSAGE_LINE_ENABLE, ScopeInterface::SCOPE_STORE);
        if ($enabled == null || $enabled == '0') {
            $isEnabled = false;
        }
        return $isEnabled;
    }

    /**
     * Get line channel id
     * @return mixed
     */
    public function getLineChannelId()
    {
        return $this->scopeConfig->getValue(self::XML_LINE_MESSAGE_LINE_CHANNEL_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get line access token
     * @param $websiteId
     * @return mixed
     */
    public function getLineAccessToken($websiteId)
    {
        return $this->scopeConfig->getValue(
            self::XML_LINE_MESSAGE_ACCESS_TOKEN,
            ScopeInterface::SCOPE_STORE,
            $websiteId
        );
    }

    /**
     * Get channel secret id
     * @return mixed
     */
    public function getLineSecretId()
    {
        return $this->scopeConfig->getValue(self::XML_LINE_MESSAGE_LINE_CHANNEL_SECRET, ScopeInterface::SCOPE_STORE);
    }
}
