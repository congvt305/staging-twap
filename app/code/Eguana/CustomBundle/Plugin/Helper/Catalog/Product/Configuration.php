<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 5/8/20
 * Time: 7:50 PM
 */
namespace Eguana\CustomBundle\Plugin\Helper\Catalog\Product;

use Magento\Bundle\Helper\Catalog\Product\Configuration as ConfigurationAlias;

/**
 * This class is used for after plugin in which bundle options are changing
 *
 * Class Configuration
 */
class Configuration
{
    /**
     * Get formated bundled selections
     *
     * Returns array of options objects.
     * Each option object will contain array of selections objects
     *
     * @return array
     */
    public function afterGetBundleOptions(ConfigurationAlias $subject, $result)
    {
        foreach ($result as $key => $value) {
            $str = $result[$key]['value'][0];
            $explodedString = explode("x", $str);
            $explodedSecondString = explode("<span", $explodedString[1]);
            $result[$key]['value'][0] = $explodedSecondString[0] . 'x' . $explodedString[0] . '<span' . $explodedSecondString[1];
        }
        return $result;
    }
}
