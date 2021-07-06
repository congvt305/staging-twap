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

use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Eguana\RedInvoice\Model\RedInvoiceConfig\RedInvoiceConfig;

/**
 * LayoutProcessor Plugin to modify layout
 * Class LayoutProcessorPlugin
 */
class LayoutProcessorPlugin
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManage;

    /**
     * @var RedInvoiceConfig
     */
    private $redInvoiceConfig;

    /**
     * LayoutProcessorPlugin constructor.
     * @param StoreManagerInterface $storeManage
     * @param RedInvoiceConfig $redInvoiceConfig
     */
    public function __construct(
        StoreManagerInterface $storeManage,
        RedInvoiceConfig $redInvoiceConfig
    ) {
        $this->storeManage = $storeManage;
        $this->redInvoiceConfig = $redInvoiceConfig;
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
        }
        return $jsLayout;
    }
}
