<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/10/20
 * Time: 6:15 PM
 */
namespace Eguana\StoreLocator\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AvailableForRedemption implements OptionSourceInterface
{
    /**
     * @const AVAILABLE_FOR_REDEMPTION
     */
    const AVAILABLE_FOR_REDEMPTION = 1;

    /**
     * @const NOT_AVAILABLE_FOR_REDEMPTION
     */
    const NOT_AVAILABLE_FOR_REDEMPTION = 0;

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
        return [self::AVAILABLE_FOR_REDEMPTION => __('Available'),
            self::NOT_AVAILABLE_FOR_REDEMPTION => __('Not Available')];
    }
}
