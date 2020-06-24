<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 16/6/20
 * Time: 12:01 PM
 */
namespace Eguana\VideoBoard\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * This class is used to get the sort order value
 *
 * Class Dropdown
 * Eguana\VideoBoard\Model\Config\Source
 */
class Dropdown implements OptionSourceInterface
{
    /**
     * Values for config dropdown
     *
     * @return array|array[]
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
