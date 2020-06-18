<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 6. 18
 * Time: 오후 6:17
 */

namespace Amore\CustomerRegistration\Plugin\Customer\Block\Widget;

/**
 * Plugin to fix the DOB format issue while register
 * Class Dob
 * @package Amore\CustomerRegistration\Plugin\Customer\Block\Widget
 */
class Dob
{
    /**
     * While register adding date giving JavaScript warning invalid date.
     * this warning is coming from the file lib/web/mage/validation.js at line 1035. There is momentJs library which
     * validate the format of date. In our case vendor/magento/framework/Stdlib/DateTime/Timezone.php from line 120
     * using PHP class IntlDateFormatter based on locale which is zh_Hant_TW it return y/M/d according to which date
     * should be like 20/Jun/18. But momentJS supposing it should be Y/mm/dd or something else which is generated
     * by Datepicker and acceptable by momentJS.
     *
     * @param \Magento\Customer\Block\Widget\Dob $subject
     * @param $result
     * @return string
     */
    public function afterGetDateFormat(\Magento\Customer\Block\Widget\Dob $subject, $result)
    {
        return $result == 'y/M/d'?'yy/mm/dd':$result;
    }
}
