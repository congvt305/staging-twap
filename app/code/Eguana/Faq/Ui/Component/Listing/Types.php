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
 * Class Types
 *
 * Eguana\Faq\Ui\Component\Listing
 */
class Types implements ArrayInterface
{
    /**
     * @var
     */
    private $typeOptions;

    /**
     * Is Type Option
     * @return array
     */
    public function toOptionArray()
    {
        $this->typeOptions = [
            [
                'label' => __('General'),
                'value' => 1
            ]
        ];
        return $this->typeOptions;
    }
}
