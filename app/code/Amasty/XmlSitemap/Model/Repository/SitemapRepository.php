<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Repository;

use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Api\SitemapRepositoryInterface;
use Amasty\XmlSitemap\Model\ResourceModel\Sitemap as SitemapResource;
use Amasty\XmlSitemap\Model\SitemapFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class SitemapRepository implements SitemapRepositoryInterface
{
    /**
     * @var SitemapFactory
     */
    private $sitemapFactory;

    /**
     * @var SitemapResource
     */
    private $sitemapResource;

    public function __construct(
        SitemapFactory $sitemapFactory,
        SitemapResource $sitemapResource
    ) {
        $this->sitemapFactory = $sitemapFactory;
        $this->sitemapResource = $sitemapResource;
    }

    public function save(SitemapInterface $sitemap): SitemapInterface
    {
        try {
            $id = $sitemap->getSitemapId();

            if ($id) {
                $data = $sitemap->getData();
                $sitemap = $this->getById($id);
                $sitemap->addData($data);
            }
            $this->sitemapResource->save($sitemap);
        } catch (\Exception $e) {
            if ($sitemap->getSitemapId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save sitemap with ID %1. Error: %2',
                        [$sitemap->getSitemapId(), $e->getMessage()]
                    )
                );
            }

            throw new CouldNotSaveException(__('Unable to save new sitemap. Error: %1', $e->getMessage()));
        }

        return $sitemap;
    }

    public function getById(int $id): SitemapInterface
    {
        $sitemap = $this->sitemapFactory->create();
        $this->sitemapResource->load($sitemap, $id);

        if (!$sitemap->getSitemapId()) {
            throw new NoSuchEntityException(__('Sitemap with specified ID "%1" not found.', $id));
        }

        return $sitemap;
    }

    public function delete(SitemapInterface $sitemap): bool
    {
        try {
            $this->sitemapResource->delete($sitemap);
        } catch (\Exception $e) {
            if ($sitemap->getSitemapId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove sitemap with ID %1. Error: %2',
                        [$sitemap->getSitemapId(), $e->getMessage()]
                    )
                );
            }

            throw new CouldNotDeleteException(__('Unable to remove sitemap. Error: %1', $e->getMessage()));
        }

        return true;
    }

    public function deleteById(int $id): bool
    {
        $sitemapModel = $this->getById($id);
        $this->delete($sitemapModel);

        return true;
    }
}
