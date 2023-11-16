<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Ui\DataProvider\Form;

use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Model\OptionSource\AdditionalEntities;
use Amasty\XmlSitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use Amasty\XmlSitemap\Model\Sitemap;
use Amasty\XmlSitemap\Model\Sitemap\SitemapEntityData;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class SitemapDataProvider extends AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var AdditionalEntities
     */
    private $additionalEntities;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        AdditionalEntities $additionalEntities,
        array $meta = [],
        array $data = []
    ) {
        $this->request = $request;
        $this->collection = $collectionFactory->create();
        $this->additionalEntities = $additionalEntities;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        $sitemap = $this->getSitemap();
        if ($sitemap) {
            $sitemapId = $sitemap->getId();
            $data[$sitemapId] = $sitemap->toArray();

            /** @var SitemapEntityData $entity */
            foreach (($data[$sitemapId][SitemapInterface::ENTITIES] ?? []) as $entity) {
                $entityData = $entity->toArray();

                if (in_array($entity->getCode(), AdditionalEntities::DEFAULT_ENTITIES)) {
                    $data[$sitemapId][$entity->getCode()] = $entityData;
                } else {
                    $data[$sitemapId]['additional']['entities'][] = $entityData;
                }

            }
        }

        return $data ?? [];
    }

    public function getMeta(): array
    {
        $meta = parent::getMeta();
        $additionalEntities = $this->additionalEntities->toOptionArray();
        $availableEntities = [];
        $sitemap = $this->getSitemap();

        foreach ($additionalEntities as $entity) {
            $availableEntities[$entity['value']] = [
                'allowed' => $this->isAllowedEntity($sitemap, $entity['value']),
                'label' => $entity['label'],
                'value' => $entity['value']
            ];
        }

        if (empty($additionalEntities)) {
            $meta['additional']['children']['entities']['arguments']['data']['config'] = [
                'visible' => false,
                'disabled' => true
            ];
        } else {
            $meta['additional']['children']['entities']['arguments']['data']['config'] = [
                'availableEntities' => $availableEntities
            ];

            $meta['additional']['children']['warning']['arguments']['data']['config'] = [
                'visible' => false
            ];
        }

        return $meta;
    }

    private function isAllowedEntity(?SitemapInterface $sitemap, string $value): bool
    {
        return !$sitemap || !isset($sitemap->getEntities()[$value]);
    }

    private function getSitemap(): ?SitemapInterface
    {
        return $this->collection->getItemById($this->request->getParam(SitemapInterface::SITEMAP_ID));
    }
}
