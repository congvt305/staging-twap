<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class CmsRelation implements OptionSourceInterface
{
    const ID = 'page_id';
    const UUID = 'amseo-uuid';
    const IDENTIFIER = 'identifier';

    public function toOptionArray(): array
    {
        return [
            ['value' => self::ID, 'label' => __('By ID')],
            ['value' => self::UUID, 'label' => __('By Hreflang UUID')],
            ['value' => self::IDENTIFIER, 'label' => __('By URL Key (Page Identifier)')]
        ];
    }
}
