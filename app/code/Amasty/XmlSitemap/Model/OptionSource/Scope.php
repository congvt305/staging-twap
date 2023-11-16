<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class Scope implements OptionSourceInterface
{
    const SCOPE_GLOBAL = '0';
    const SCOPE_WEBSITE = '1';

    public function toOptionArray(): array
    {
        return [
            ['value' => self::SCOPE_GLOBAL, 'label' => __('Global')],
            ['value' => self::SCOPE_WEBSITE, 'label' => __('Website')]
        ];
    }
}
