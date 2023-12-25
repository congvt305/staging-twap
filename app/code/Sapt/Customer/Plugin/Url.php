<?php

namespace Sapt\Customer\Plugin;

use Magento\Framework\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Url
{
    const CONFIG_PATH = 'sapt_mypage/general/enable';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig
    ){
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    public function afterGetDashboardUrl(
        \Magento\Customer\Model\Url $subject,
        $result
    ) {
        if($this->scopeConfig->getValue(self::CONFIG_PATH,ScopeInterface::SCOPE_STORE)) {
            return $this->urlBuilder->getUrl('customer/account/dashboard');
        }

        return $result;
    }

    public function afterGetAccountUrl(
        \Magento\Customer\Model\Url $subject,
        $result
    ) {
        if($this->scopeConfig->getValue(self::CONFIG_PATH,ScopeInterface::SCOPE_STORE)) {
            return $this->urlBuilder->getUrl('customer/account/dashboard');
        }

        return $result;
    }
}
