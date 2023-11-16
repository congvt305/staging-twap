<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class Frequency implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $options = [];

        foreach ($this->toArray() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }

        return $options;
    }

    public function toArray(): array
    {
        return [
            'always' => __('Always'),
            'hourly' => __('Hourly'),
            'daily' => __('Daily'),
            'weekly' => __('Weekly'),
            'monthly' => __('Monthly'),
            'yearly' => __('Yearly'),
            'never' => __('Never'),
        ];
    }
}
