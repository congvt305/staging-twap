<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/22/20
 * Time: 12:20 AM
 */
namespace Eguana\Magazine\Ui\Component\Listing;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
 *
 * Class Type
 */
class Type implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {

        return [
            ['value' => 1,  'label' => __('Main Banner')],
            ['value' => 2, 'label' => __('Image')],
            ['value' => 3,   'label' => __('Video')]
        ];
    }
}
