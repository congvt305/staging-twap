<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/11/20
 * Time: 7:38 AM
 */

namespace Eguana\Magazine\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
 *
 * Class Dropdown
 */
class Dropdown implements OptionSourceInterface
{
    /**
     * Values for config dropdown
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 0,
                'label' => __('Ascending')
            ],
            [
                'value' => 1,
                'label' => __('Descending')
            ]
        ];
    }
}
