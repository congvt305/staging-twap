<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/27/20
 * Time: 10:48 AM
 */

namespace Eguana\GWLogistics\Plugin\Checkout;


class LayoutProcessor
{
    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    private $helper;

    public function __construct(\Eguana\GWLogistics\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param $result
     * @param array $jsLayout
     */
    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $subject, $result, $jsLayout)
    {
        $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['after-shipping-method-item']['children']
        ['gwlogistics-cvs']['config']['merchantId'] = $this->helper->getMerchantId();

        $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['after-shipping-method-item']['children']
        ['gwlogistics-cvs']['config']['mapUrl'] = $this->helper->getMapUrl();

        return $result;

    }

}
