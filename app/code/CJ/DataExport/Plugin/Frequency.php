<?php

namespace CJ\DataExport\Plugin;

/**
 * Class Frequency
 */
class Frequency
{
    /**
     * @var \CJ\DataExport\Model\Config\Source\Frequency
     */
    protected $helper;

    /**
     * @param \CJ\DataExport\Model\Config\Source\Frequency $helper
     */
    public function __construct(\CJ\DataExport\Model\Config\Source\Frequency $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\ScheduledImportExport\Model\Scheduled\Operation\Data $subject
     * @param array $result
     * @return array
     */
    public function afterGetFrequencyOptionArray($subject, $result)
    {
        return $this->helper->toOptionArray();
    }
}
