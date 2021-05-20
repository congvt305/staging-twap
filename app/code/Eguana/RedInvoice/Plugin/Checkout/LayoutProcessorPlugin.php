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

/**
 * LayoutProcessor Plugin to modify layout
 * Class LayoutProcessorPlugin
 */
class LayoutProcessorPlugin
{
    const VN_WEBSITE = "vn_laneige_website";

    /**
     * @var StoreManagerInterface
     */
    private $_storeManage;

    /**
     * LayoutProcessorPlugin constructor.
     * @param StoreManagerInterface $StoreManage
     */
    public function __construct(
        StoreManagerInterface $StoreManage
    ) {
        $this->_storeManage = $StoreManage;
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
        $current_website = $this->_storeManage->getWebsite()->getCode();
        if ($current_website == self::VN_WEBSITE) {

            $jsLayout['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shippingAdditional']['children']['redInvoiceform']['component'] =
                'Eguana_RedInvoice/js/view/checkout/shipping/red-invoice';
        }
        return $jsLayout;
    }
}
