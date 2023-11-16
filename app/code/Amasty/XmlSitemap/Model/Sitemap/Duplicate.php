<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Sitemap;

use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Api\SitemapRepositoryInterface;
use Amasty\XmlSitemap\Model\Sitemap;

class Duplicate
{
    /**
     * @var SitemapRepositoryInterface
     */
    private $sitemapRepository;

    public function __construct(SitemapRepositoryInterface $sitemapRepository)
    {
        $this->sitemapRepository = $sitemapRepository;
    }

    public function execute(int $id): void
    {
        /** @var Sitemap $model */
        $model = $this->sitemapRepository->getById($id);
        $this->modifyData($model);

        $this->sitemapRepository->save($model);
    }

    private function modifyData(Sitemap $model)
    {
        $model->unsetData(SitemapInterface::SITEMAP_ID);
        foreach ($model->getData(SitemapInterface::ENTITIES) as $entityName => $entity) {
            $model->setData($entityName, $entity->getData());
        }
    }
}
