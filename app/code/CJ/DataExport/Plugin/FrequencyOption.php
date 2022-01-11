<?php

namespace CJ\DataExport\Plugin;

/**
 * Class FrequencyOption
 */
class FrequencyOption
{
    /**
     * @param \Magento\ScheduledImportExport\Model\Scheduled\Operation\Data $subject
     * @param array $result
     * @return array
     */
    public function afterGetFrequencyOptionArray($subject, $result) {
        array_unshift($result, [
            \CJ\DataExport\Model\Config\Source\Frequency::CRON_HALF_HOURLY => __('Every 30 minutes')
        ]);

        return $result;
    }
}
