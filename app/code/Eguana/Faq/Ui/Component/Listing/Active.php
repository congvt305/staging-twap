<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Ui\Component\Listing;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Active
 *
 * Eguana\Faq\Ui\Component\Listing
 */
class Active implements ArrayInterface
{
    /**
     * @var
     */
    private $activeOptions;

    /**
     * Is Active Option
     * @return array
     */
    public function toOptionArray()
    {
        $this->activeOptions = [
            [
                'label' => __('Enabled'),
                'value' => 1,
            ],
            [
                'label' => __('Disabled'),
                'value' => 0
            ]
        ];
        return $this->activeOptions;
    }
}
