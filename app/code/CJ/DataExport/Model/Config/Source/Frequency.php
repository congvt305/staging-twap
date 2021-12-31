<?php

namespace CJ\DataExport\Model\Config\Source;

/**
 * Class Frequency
 */
class Frequency extends \Magento\Cron\Model\Config\Source\Frequency
{
    const CRON_HALF_HOURLY = 'H';

    /**
     * {@inheritDoc}
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        array_unshift($options, ['label' => __('Every 30 minutes'), 'value' => self::CRON_HALF_HOURLY]);
        return $options;
    }
}
