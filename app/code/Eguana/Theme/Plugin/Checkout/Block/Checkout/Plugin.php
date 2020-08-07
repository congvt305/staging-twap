<?php
/**
 * Created by PhpStorm.
 * User: hyuna
 * Date: 2019-10-15
 * Time: AM 7:33
 */

namespace Eguana\Theme\Plugin\Checkout\Block\Checkout;


use Magento\Checkout\Block\Checkout\AttributeMerger;

class Plugin
{
    public function afterMerge(AttributeMerger $subject, $result)
    {
        if (array_key_exists('street', $result)) {
            $result['street']['children'][0]['placeholder'] = __('Enter the street address');
            $result['street']['children'][1]['placeholder'] = __('Enter the street address 2');
            $result['street']['children'][2]['placeholder'] = __('Enter the street address 3');
        }

        if (array_key_exists('telephone', $result)) {
            $result['telephone']['placeholder'] = __('Phone Number');
        }

        if (array_key_exists('fax', $result)) {
            $result['fax']['placeholder'] = __('Fax');
        }

        if (array_key_exists('company', $result)) {
            $result['company']['placeholder'] = __('Company');
        }

        if (array_key_exists('postcode', $result)) {
            $result['postcode']['placeholder'] = __('Zip/Postal Code');
        }

        if (array_key_exists('city', $result)) {
            $result['city']['placeholder'] = __('City');
        }

        if (array_key_exists('lastname', $result)) {
            $result['lastname']['placeholder'] = __('Last Name');
        }

        if (array_key_exists('firstname', $result)) {
            $result['firstname']['placeholder'] = __('First Name');
        }

        return $result;
    }
}
