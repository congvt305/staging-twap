<?php

namespace CJ\LineShopping\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Config
{
    const XML_PATH_LINE_ENABLE = 'line_shopping/general/enable';
    const XML_PATH_LINE_ENABLE_SAVE_UTM = 'line_shopping/general/enable_utm';
    const XML_PATH_LINE_COOKIE_LIFETIME = 'line_shopping/general/cookie_lifetime';
    const XML_PATH_LINE_TRIAL_PERIOD = 'line_shopping/general/trial_period';
    const XML_PATH_LINE_API = 'line_shopping/api/';
    const XML_PATH_LINE_STORE = 'line_shopping/setting/filepath';
    const XML_PATH_LINE_STORE_FILE = 'line_shopping/setting/';
    const XML_PATH_LINE_CUSTOMER_URL_MULTICAST_MESSAGE = 'line_shopping/customer_integration/line_url_multicast_message';
    const XML_PATH_LINE_CUSTOMER_CHANNEL_ACCESS_TOKEN = 'line_shopping/customer_integration/channel_access_token';

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    protected EncryptorInterface $encryptor;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * @param $type
     * @param $websiteId
     * @return mixed
     */
    public function getFileName($type, $websiteId = null)
    {
        $fileName = self::XML_PATH_LINE_STORE_FILE . $type;
        return $this->getConfig($fileName, $websiteId);
    }

    /**
     * @param $websiteId
     * @return mixed
     */
    public function getPathLineStore($websiteId = null)
    {
        return $this->getConfig(self::XML_PATH_LINE_STORE, $websiteId);
    }

    /**
     * @param $websiteId
     * @return mixed
     */
    public function isEnable($websiteId = null)
    {
        return $this->getConfig(self::XML_PATH_LINE_ENABLE, $websiteId);
    }

    /**
     * @param $websiteId
     * @return mixed
     */
    public function isEnableSaveUtm($websiteId = null)
    {
        return $this->getConfig(self::XML_PATH_LINE_ENABLE_SAVE_UTM, $websiteId);
    }

    /**
     * @param $websiteId
     * @return mixed
     */
    public function getCookieLifeTime($websiteId = null)
    {
        return $this->getConfig(self::XML_PATH_LINE_COOKIE_LIFETIME, $websiteId);
    }

    /**
     * @return mixed
     */
    public function getTrialPeriod()
    {
        return $this->getConfig(self::XML_PATH_LINE_TRIAL_PERIOD);
    }

    /**
     * @param $configPath
     * @param $websiteId
     * @return mixed
     */
    public function getConfig($configPath, $websiteId = null , $isEncrypt = false)
    {
        $configValue =  $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );

        if ($isEncrypt) {
            return $this->encryptor->decrypt($configValue);
        }

        return $configValue;
    }

    /**
     * @param $path
     * @param $website
     * @param bool $isEncrypt
     * @return mixed
     */
    public function getApiConfigValue($path, $website = null, bool $isEncrypt = false)
    {
        return $this->getConfig(self::XML_PATH_LINE_API . $path, $website, $isEncrypt);
    }

    /**
     * @param $websiteId
     * @return mixed
     */
    public function getUrlSendMulticastMessage($websiteId = null)
    {
        return $this->getConfig(self::XML_PATH_LINE_CUSTOMER_URL_MULTICAST_MESSAGE, $websiteId);
    }

    /**
     * @param $websiteId
     * @return mixed
     */
    public function getChannelAccessToken($websiteId = null)
    {
        return $this->getConfig(self::XML_PATH_LINE_CUSTOMER_CHANNEL_ACCESS_TOKEN, $websiteId);
    }
}
