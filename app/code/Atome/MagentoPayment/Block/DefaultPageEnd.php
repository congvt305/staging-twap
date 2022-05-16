<?php
/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */
namespace Atome\MagentoPayment\Block;

use Atome\MagentoPayment\Model\Config\LocaleConfig;
use Magento\Framework\View\Element\Template;

class DefaultPageEnd extends Template
{
    protected $localeConfig;

    public function __construct(
        Template\Context $context,
        LocaleConfig $localeConfig,
        array $data
    ) {
        parent::__construct($context, $data);

        $this->localeConfig = $localeConfig;
    }

    public function getAppState()
    {
        return $this->_appState;
    }

    public function getStoreManager()
    {
        return $this->_storeManager;
    }

    public function getScopeConfig()
    {
        return $this->_scopeConfig;
    }

    public function getAtomeLogoName()
    {        
        return $this->localeConfig->getAtomeLogo();
    }

    public function getAtomePaymentLogoName()
    {        
        return $this->localeConfig->getCheckoutLogo();
    }

    public function getMinimumSpend()
    {
        return $this->localeConfig->getMinimumSpend();
    }

    public function getCountryConfig()
    {
        return $this->localeConfig->getCountryConfig();
    }
}
