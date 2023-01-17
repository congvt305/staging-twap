<?php

namespace CJ\CustomAmastyReview\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 */
class Config
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $path
     * @param $websiteId
     * @return mixed
     */
    public function getGeneralConfig($path, $websiteId = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * @param $path
     * @param $websiteId
     * @return mixed
     */
    public function getModuleConfig($path, $websiteId = null)
    {
        return $this->getGeneralConfig('amasty_advancedreview/' . $path, $websiteId);
    }

    /**
     * @param int|null $websiteId
     * @return bool
     */
    public function isAllowCoupons(?int $websiteId)
    {
        return (bool)$this->getModuleConfig('coupons/enabled', $websiteId);
    }

    /**
     * @return bool
     */
    public function isNeedReview(?int $websiteId)
    {
        return (bool)$this->getModuleConfig('coupons/review', $websiteId);
    }
}
