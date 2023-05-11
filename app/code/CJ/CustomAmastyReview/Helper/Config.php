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
     * @param $storeId
     * @return mixed
     */
    protected function getGeneralConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param $path
     * @param $storeId
     * @return mixed
     */
    public function getModuleConfig($path, $storeId = null)
    {
        return $this->getGeneralConfig('amasty_advancedreview/' . $path, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isAllowCoupons(?int $storeId)
    {
        return (bool)$this->getModuleConfig('coupons/enabled', $storeId);
    }

    /**
     * @return bool
     */
    public function isNeedReview(?int $storeId)
    {
        return (bool)$this->getModuleConfig('coupons/review', $storeId);
    }


}
