<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 22/9/20
 * Time: 4:45 PM
 */
namespace Eguana\CustomRMA\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterfaceAlias;
use Magento\Framework\View\Element\Block\ArgumentInterface as ArgumentInterfaceAlias;
use Magento\Store\Model\ScopeInterface as ScopeInterfaceAlias;

/**
 * This viewModel class is used to get the store phone number
 *
 * Class GetStoreInfo
 */
class GetStoreInfo implements ArgumentInterfaceAlias
{
    /**
     * @var ScopeConfigInterfaceAlias
     */
    private $scopeConfig;

    /**
     * GetStoreInfo constructor.
     * @param ScopeConfigInterfaceAlias $scopeConfig
     */
    public function __construct(ScopeConfigInterfaceAlias $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * This method is used to get the store phone number
     * @return mixed
     */
    public function getStorePhoneNumber()
    {
        return $this->scopeConfig->getValue(
            'general/store_information/phone',
            ScopeInterfaceAlias::SCOPE_STORE
        );
    }
}
