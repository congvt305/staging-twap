<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/24/20
 * Time: 6:17 AM
 */

namespace Eguana\CustomCheckout\Plugin;


use Magento\Checkout\Block\Checkout\AttributeMerger;

class CustomAddressSortOrder
{
    /**
     * @param \Magento\Checkout\Block\Checkout\AttributeMerger $subject
     * @param array $elements
     * @param string $providerName
     * @param string $dataScopePrefix
     * @param array $fields
     * @return array
     */
    public function beforeMerge(\Magento\Checkout\Block\Checkout\AttributeMerger $subject, $elements, $providerName, $dataScopePrefix, array $fields = [])
    {
        if (isset($elements['lastname'], $elements['street'], $elements['city'], $elements['city_id']))
        {
            $elements['lastname']['sortOrder'] = '15';
            $elements['street']['sortOrder'] = '107';
            $elements['city']['sortOrder'] = '105';
            $elements['city_id']['sortOrder'] = '105';
        }
        return [$elements, $providerName, $dataScopePrefix, $fields];
    }
}
