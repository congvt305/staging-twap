<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 30/9/20
 * Time: 06:48 PM
 */
declare(strict_types=1);

namespace Eguana\CustomerBulletin\Model\Ticket\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ticket status options
 */
class Status implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray() : array
    {
        return [
            ['value' => 1, 'label' => __('Open')],
            ['value' => 0, 'label' => __('Close')]
        ];
    }
}
