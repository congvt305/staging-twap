<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\OptionSource;

use Amasty\XmlSitemap\Model\Sitemap\SourceProvider;
use Magento\Framework\Data\OptionSourceInterface;

class AdditionalEntities implements OptionSourceInterface
{
    const DEFAULT_ENTITIES = ['product', 'category', 'cms', 'extra'];

    /**
     * @var SourceProvider
     */
    private $sourceProvider;

    public function __construct(
        SourceProvider $sourceProvider
    ) {
        $this->sourceProvider = $sourceProvider;
    }

    public function toOptionArray(): array
    {
        $result = [];
        $sources = $this->sourceProvider->getAllSources();
        $additionalSources = array_diff_key($sources, array_flip(self::DEFAULT_ENTITIES));

        foreach ($additionalSources as $source) {
            $result[] = ['value' => $source->getEntityCode(), 'label' => $source->getEntityLabel()];
        }

        return $result;
    }

    public function toArray(): array
    {
        return array_column($this->toOptionArray(), 'value');
    }
}
