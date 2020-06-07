<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/5/20
 * Time: 5:57 PM
 */

namespace Eguana\GWLogistics\Plugin;


class LayoutProcessor
{

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param $result
     * @param array $jsLayout
     */
    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $subject, $result, $jsLayout)
    {
//        $quote = $this->checkoutSession->getQuote();

//        $shippingConfig = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
//        ['children']['shippingAddress']['children']['before-shipping-method-form']['children']['shippingAdditional'];

        $shippingConfig = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress'];

//        $validation['required-entry'] = true;

        $shippingConfig['children']['cvs-form'] = [
            'component' => "Eguana_GWLogistics/js/view/cvs-form",
            'sortOrder' => 0,
        ];

//        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
//        ['shippingAddress']['children']['custom-shipping-method-fields']['children']['input_custom_shipping_field'] = [
//            'component' => "Magento_Ui/js/form/element/abstract",
//            'config' => [
//                'customScope' => 'customShippingMethodFields',
//                'template' => 'ui/form/field',
//                'elementTmpl' => "ui/form/element/input",
//                'id' => "input_custom_shipping_field"
//            ],
//            'dataScope' => 'customShippingMethodFields.custom_shipping_field[input_custom_shipping_field]',
//            'label' => "Input option",
//            'provider' => 'checkoutProvider',
//            'visible' => true,
//            'validation' => $validation,
//            'sortOrder' => 2,
//            'id' => 'custom_shipping_field[input_custom_shipping_field]'
//        ];


        return $jsLayout;
    }
}
