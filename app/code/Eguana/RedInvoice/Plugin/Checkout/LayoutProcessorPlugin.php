<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 5/11/21
 * Time: 10:10 AM
 */
namespace Eguana\RedInvoice\Plugin\Checkout;

use Amasty\CheckoutCore\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Eguana\RedInvoice\Model\RedInvoiceConfig\RedInvoiceConfig;

/**
 * LayoutProcessor Plugin to modify layout
 * Class LayoutProcessorPlugin
 */
class LayoutProcessorPlugin
{
    const STORE_CODE_VN_LANEIGE = 'vn_laneige';
    /**
     * @var StoreManagerInterface
     */
    private $storeManage;

    /**
     * @var RedInvoiceConfig
     */
    private $redInvoiceConfig;

    /**
     * @var Config
     */
    private $amastyConfig;

    /**
     * @param StoreManagerInterface $storeManage
     * @param RedInvoiceConfig $redInvoiceConfig
     * @param Config $amastyConfig
     */
    public function __construct(
        StoreManagerInterface $storeManage,
        RedInvoiceConfig $redInvoiceConfig,
        Config $amastyConfig
    ) {
        $this->storeManage = $storeManage;
        $this->redInvoiceConfig = $redInvoiceConfig;
        $this->amastyConfig = $amastyConfig;
    }

    /**
     * @param LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        LayoutProcessor $subject,
        array  $jsLayout
    ) {
        $websiteId = $this->storeManage->getWebsite()->getId();
        $isModuleEnabled = $this->redInvoiceConfig->getEnableValue($websiteId);
        if ($isModuleEnabled) {
            $jsLayout['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shippingAdditional']['children']['redInvoiceform']['component'] =
                'Eguana_RedInvoice/js/view/checkout/shipping/red-invoice';
            if ($this->storeManage->getStore()->getCode() == self::STORE_CODE_VN_LANEIGE &&
                $this->amastyConfig->isEnabled()
            ) {
                $jsLayout['components']['checkout']['children']['steps']['children']
                ['shipping-step']['children']['shippingAddress']['children']
                ['shippingAdditional']['children']['redInvoiceform']['sortOrder'] = 3;
            }
        }
        return $jsLayout;
    }
}
