<?php

namespace Eguana\InventoryCompensation\Model\Config\Source;

class Frequency extends \Magento\Cron\Model\Config\Source\Frequency
{
    /**
     * @var array
     */
    protected static $_options;

    const CRON_DAILY = 'D';

    const CRON_WEEKLY = 'W';

    const CRON_MONTHLY = 'M';

    const CUSTOM_CRON = 'C';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!self::$_options) {
            self::$_options = [
                ['label' => __('Daily'), 'value' => self::CRON_DAILY],
                ['label' => __('Weekly'), 'value' => self::CRON_WEEKLY],
                ['label' => __('Monthly'), 'value' => self::CRON_MONTHLY],
                ['label' => __('Custom Cron'), 'value' => self::CUSTOM_CRON],
            ];
        }
        return self::$_options;
    }
}
