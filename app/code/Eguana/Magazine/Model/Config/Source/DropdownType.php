<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/17/20
 * Time: 6:43 AM
 */
namespace Eguana\Magazine\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * This class is used for drop down Button for different magazine type
 * Class DropdownType
 */
class DropdownType implements OptionSourceInterface
{
    /**
     * Values for config dropdown
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => __('Main Banner')
            ],
            [
                'value' => 2,
                'label' => __('Image')
            ],
            [
                'value' => 3,
                'label' => __('Video')
            ]
        ];
    }
}
