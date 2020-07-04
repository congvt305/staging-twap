<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/14/20
 * Time: 9:39 AM
 */

namespace Eguana\Directory\Plugin\Checkout;

class BillingAddressLayoutProcessor
{
    private $result;

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param $result
     * @param array $jsLayout
     */
    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $subject, $result, $jsLayout)
    {
        $this->result = $result;

        $paymentConfiguration = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children']['renders']['children'];

        $billingAddressPaymentForms = [];
        foreach ($paymentConfiguration as $payment => $config) {
            foreach ($config['methods'] as $methodCode => $paymentComponent) {
                if ($paymentComponent['isBillingAddressRequired'] === true) {
                    $billingAddressPaymentForms[] = $methodCode . '-form';
                }
            }
        }

        $paymentForms = $result['components']['checkout']['children']['steps']['children']
        ['billing-step']['children']['payment']['children']
        ['payments-list']['children'];

        $paymentMethodForms = array_keys($paymentForms);

        if (!isset($paymentMethodForms)) {
            return $result;
        }

        foreach ($paymentMethodForms as $paymentMethodForm) {
            if (!in_array($paymentMethodForm, $billingAddressPaymentForms)) {
                continue;
            }
            $paymentMethodCode = str_replace(
                '-form',
                '',
                $paymentMethodForm,
                $paymentMethodCode
            );
            $this->addField($paymentMethodForm, $paymentMethodCode);
        }

        return $this->result;
    }

    /**
     * @param $paymentMethodForm
     * @param $paymentMethodCode
     * @return $this
     */
    private function addField($paymentMethodForm, $paymentMethodCode)
    {
        $cityIdPassed = $this->result['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children']['payments-list']['children'][$paymentMethodForm]['children']
        ['form-fields']['children']['city_id'];

        $cityField = [
            'component' => 'Eguana_Directory/js/city', //need to change
            'config' => [
                'customScope' => 'billingAddresscheckmo', //passed
                'template' => 'ui/form/field', //passed
                'elementTmpl' => 'ui/form/element/select', //need to change
                'templateice' => 'ui/form/field', //need to add
                'customEntry' => 'billingAddress'. $paymentMethodCode. '.city',//need to add
            ],
            'dataScope' => 'billingAddresscheckmo' . '.' . 'city_id', //exists
            'label' => $cityIdPassed['label'], //exists
            'provider' => 'checkoutProvider', //exists
            'sortOrder' => '80',//exists
            'validation' => ['required-entry' => true], //change
            'options' => $cityIdPassed['options'], //exists
            'filterBy' => [ //exists
                'target' => '${ $.provider }:${ $.parentScope }.region_id',
                'field' => 'region_id',
            ],
            'customEntry' => null,
            'visible' => true, //passed
            'deps' => ['checkoutProvider'], //
            'imports' => [
                'initialOptions' => 'index = checkoutProvider:dictionaries.city_id',
                'setOptions' => 'index = checkoutProvider:dictionaries.city_id'
            ],
        ];

        $this->result['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children']['payments-list']['children'][$paymentMethodForm]['children']
        ['form-fields']['children']['city_id'] = $cityField;

        return $this;
    }

    //test later.
    private function mergeConfigurationNode($nodeName, array $mainSource, array $additionalSource)
    {
        $mainData = isset($mainSource[$nodeName]) ? $mainSource[$nodeName] : [];
        $additionalData = isset($additionalSource[$nodeName]) ? $additionalSource[$nodeName] : [];
        return array_replace_recursive($additionalData, $mainData);
    }
}
