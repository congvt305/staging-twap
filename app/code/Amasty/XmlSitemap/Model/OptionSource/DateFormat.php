<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class DateFormat implements OptionSourceInterface
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
            'Y-m-d\TH:i:sP' => __('With time'),
            'Y-m-d' => __('Without time'),
        ];
    }
}
