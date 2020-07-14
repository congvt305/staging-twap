<?php
/**
 *  @author Eguana Team
 *  @copyriht Copyright (c) ${YEAR} Eguana {http://eguanacommerce.com}
 *  Created byPhpStorm
 *  User:  kashif
 *  Date: 5/03/20
 *  Time: 10:30 am
 */

namespace Amore\CustomerRegistration\Block\Widget;

/**
 * Dob preference to change the dob validation
 *
 * Class Dob
 */
class Dob extends \Magento\Customer\Block\Widget\Dob
{
    /**
     * While register adding date giving JavaScript warning invalid date.
     * this warning is coming from the file lib/web/mage/validation.js at line 1035. There is momentJs library which
     * validate the format of date. In our case vendor/magento/framework/Stdlib/DateTime/Timezone.php from line 120
     * using PHP class IntlDateFormatter based on locale which is zh_Hant_TW it return y/M/d according to which date
     * should be like 20/Jun/18. But momentJS supposing it should be Y/mm/dd or something else which is generated
     * by Datepicker and acceptable by momentJS.
     *
     * Return data-validate rules
     *
     * @return string
     */
    public function getHtmlExtraParams()
    {
        $validators = [];
        if ($this->isRequired()) {
            $validators['required'] = true;
        }
        $validators['validate-dob-custom'] = [
            'dateFormat' => $this->getDateFormat()
        ];

        return 'data-validate="' . $this->_escaper->escapeHtml(json_encode($validators)) . '"';
    }
}
