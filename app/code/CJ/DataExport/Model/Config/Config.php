<?php

namespace CJ\DataExport\Model\Config;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 */
class Config
{
    const XML_PATH_ENABLE = 'cj_scheduleexport/general/enable';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get Value of Configurations from Admin
     *
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getModuleEnable($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_ENABLE, $storeId);
    }
}
