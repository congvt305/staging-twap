<?php

namespace Amore\StaffReferral\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const PATH_DEFAULT_BC_REFERRAL_CODE = 'points_integration/referral_setting/default_bc_referral_code';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        Context $context
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }

    /**
     * @param $path
     * @param $websiteId
     * @return mixed
     */
    private function getScopeValue($path, $websiteId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * @param $websiteId
     * @return mixed
     */
    public function getDefaultBcReferralCode($websiteId = null)
    {
        return $this->getScopeValue(self::PATH_DEFAULT_BC_REFERRAL_CODE, $websiteId);
    }
}
