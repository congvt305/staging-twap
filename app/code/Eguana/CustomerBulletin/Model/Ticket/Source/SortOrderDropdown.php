<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/11/20
 * Time: 12:01 PM
 */
declare(strict_types=1);

namespace Eguana\CustomerBulletin\Model\Ticket\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * This class is used to get the sort order value
 *
 * Class SortOrderDropdown
 */
class SortOrderDropdown implements OptionSourceInterface
{
    /**
     * Values for config dropdown
     *
     * @return array
     */
    public function toOptionArray()
    {
        return[
            ['label' => __('Ascending'), 'value' => 'asc'],
            ['label' => __('Descending'), 'value' => 'desc']
        ];
    }
}
