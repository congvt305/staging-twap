<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 1/7/21
 * Time: 5:57 PM
 */
namespace Amore\GcrmDataExport\Model\Scheduled\Operation;

use Magento\ScheduledImportExport\Model\Scheduled\Operation\Data as DataAlias;
use Magento\Cron\Model\Config\Source\Frequency;

/**
 * Return Cron Data
 * Class Data
 */
class Data extends DataAlias
{
    const CUSTOM_CRON = 'C';

    /**
     * Get frequencies option array
     * @return array
     */
    public function getFrequencyOptionArray()
    {
        return [
            Frequency::CRON_DAILY => __('Daily'),
            Frequency::CRON_WEEKLY => __('Weekly'),
            Frequency::CRON_MONTHLY => __('Monthly'),
            self::CUSTOM_CRON => __('Custom Cron')
        ];
    }
}
