<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: shahroz
 * Date: 23/1/20
 * Time: 1:12 PM
 */
namespace Eguana\StoreLocator\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * This class is used to give option to select stores per page in admin
 *
 * Class Pagination
 */
class Pagination implements OptionSourceInterface
{
    /**
     * Values for Inactive action
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getLimits();
    }

    /**
     * Get formatted number of stores to show per page
     *
     * @param int $monthsMin
     * @param int $monthsMax
     * @param int $monthsInc
     * @return array
     */
    public function getLimits()
    {
        return [
            [
                'value' => 5,
                'label' => __('Show 5 stores per page')
            ],
            [
                'value' => 10,
                'label' => __('Show 10 stores per page')
            ],
            [
                'value' => 15,
                'label' => __('Show 15 stores per page')
            ],
        ];
    }
}
