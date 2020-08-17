<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 17/8/20
 * Time: 12:56 PM
 */
namespace Eguana\Faq\Model\FaqConfiguration\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * This class is used to get the sort order value
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
