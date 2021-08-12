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
use Magento\Store\Model\StoreManagerInterface;

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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * GetStoreInfo constructor.
     * @param ScopeConfigInterfaceAlias $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterfaceAlias $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
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

    /**
     * This method is used to get the store code
     * @return mixed
     */
    public function getStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }
}
