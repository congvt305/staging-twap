<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/4/20
 * Time: 7:26 PM
 */

namespace Eguana\Directory\Plugin\Locale;

use Magento\Framework\Locale\Format;

class PriceFormatter
{

    /**
     * @param \Magento\Framework\Locale\Format $subject
     * @param $result
     * @param string|null $localeCode
     * @param string|null $currencyCode
     */
    public function afterGetPriceFormat(\Magento\Framework\Locale\Format $subject, $result, $localeCode = null, $currencyCode = null)
    {
        if ($localeCode == 'zh_Hant_TW' && isset($result['precision']) && isset($result['requiredPrecision'])) {
            $result['precision'] = 0;
            $result['requiredPrecision'] = 0;

        }
        return $result;
    }
}
