<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Cron;

use Amasty\XmlSitemap\Model\GenerateAndSave;
use Amasty\XmlSitemap\Model\ResourceModel\Sitemap\CollectionFactory as SitemapCollectionFactory;

class GenerateSitemap
{
    /**
     * @var SitemapCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var GenerateAndSave
     */
    private $generateAndSave;

    public function __construct(
        SitemapCollectionFactory $collectionFactory,
        GenerateAndSave $generateAndSave
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->generateAndSave = $generateAndSave;
    }

    public function execute(): void
    {
        $collection = $this->collectionFactory->create();

        foreach ($collection as $sitemap) {
            $this->generateAndSave->execute($sitemap);
        }
    }
}
