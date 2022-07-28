<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/14/20
 * Time: 9:39 AM
 */

namespace Eguana\Directory\Plugin\Checkout;

use Magento\Store\Model\StoreManagerInterface;

class BillingAddressLayoutProcessor
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    private $result;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

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
        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            if (!isset($this->result['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$paymentMethodForm]['children']
                ['form-fields']['children']['city_id'])) {
                return $this;
            }
        }

        $cityIdPassed = $this->result['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children']['payments-list']['children'][$paymentMethodForm]['children']
        ['form-fields']['children']['city_id'];

        $cityIdField = [
            'component' => 'Eguana_Directory/js/city', //need to change
            'config' => [
                'customScope' => 'billingAddress' . $paymentMethodCode, //passed
                'template' => 'ui/form/field', //passed
                'elementTmpl' => 'ui/form/element/select', //need to change
                'templateice' => 'ui/form/field', //need to add
                'customEntry' => 'billingAddress'. $paymentMethodCode . '.city',//need to add
            ],
            'dataScope' => 'billingAddress' . $paymentMethodCode . '.city_id', //exists
            'label' => $cityIdPassed['label'], //exists
            'provider' => 'checkoutProvider', //exists
            'sortOrder' => '105',//exists
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
        ['form-fields']['children']['city_id'] = $cityIdField;

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
