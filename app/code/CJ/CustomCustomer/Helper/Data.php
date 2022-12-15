<?php

namespace CJ\CustomCustomer\Helper;

/**
 * Class Data
 */
class Data
{
    const XML_PATH_LOGGING_ENABLED = 'cjcustomer/group/logging';

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
     * @return bool
     */
    public function getLoggingEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_LOGGING_ENABLED);
    }
}
