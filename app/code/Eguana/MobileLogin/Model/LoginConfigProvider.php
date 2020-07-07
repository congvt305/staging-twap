<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 6/7/20
 * Time: 7:13 PM
 */
namespace Eguana\MobileLogin\Model;

use Eguana\MobileLogin\Helper\Data;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class LoginConfigProvider
 *
 * This class will return enable for mobile login
 */
class LoginConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * LoginConfigProvider constructor.
     * @param Data $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * Add mobile login enable/disable value in checkout config
     * @return array|array[]
     */
    public function getConfig()
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $isMobileLogin = $this->helper->isEnabledInFrontend($websiteId);
        return ['mobileLogin' => $isMobileLogin];
    }
}
