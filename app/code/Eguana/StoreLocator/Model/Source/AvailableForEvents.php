<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/10/20
 * Time: 6:14 PM
 */
namespace Eguana\StoreLocator\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AvailableForEvents implements OptionSourceInterface
{
    /**
     * @const AVAILABLE_FOR_EVENTS
     */
    const AVAILABLE_FOR_EVENTS = 1;

    /**
     * @const NOT_AVAILABLE_FOR_EVENTS
     */
    const NOT_AVAILABLE_FOR_EVENTS = 0;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    /**
     * @return array
     */
    private function getAvailableStatuses()
    {
        return [self::AVAILABLE_FOR_EVENTS => __('Available'),
            self::NOT_AVAILABLE_FOR_EVENTS => __('Not Available')];
    }
}
