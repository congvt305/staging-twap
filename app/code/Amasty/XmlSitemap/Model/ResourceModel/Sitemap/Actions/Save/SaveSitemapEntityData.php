<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\ResourceModel\Sitemap\Actions\Save;

use Amasty\Base\Model\Serializer;
use Amasty\XmlSitemap\Api\SitemapEntity\SitemapEntityDataInterface as EntityDataInterface;
use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Model\ResourceModel\Sitemap;
use Amasty\XmlSitemap\Model\ResourceModel\Sitemap\Actions\AdditionalActionInterface;
use Amasty\XmlSitemap\Model\Sitemap\SourceProvider;
use Magento\Framework\App\ResourceConnection;

class SaveSitemapEntityData implements AdditionalActionInterface
{
    private const ADDITIONAL_ENTITIES = 'additional/entities';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SourceProvider
     */
    private $sourceProvider;

    /**
     * @var array
     */
    private $tableFields = null;

    /**
     * @var Serializer
     */
    private $jsonSerializer;

    public function __construct(
        ResourceConnection $resourceConnection,
        SourceProvider $sourceProvider,
        Serializer $jsonSerializer
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->sourceProvider = $sourceProvider;
        $this->jsonSerializer = $jsonSerializer;
    }

    public function execute(array $sitemapArray): void
    {
        foreach ($sitemapArray as $sitemap) {
            $connection = $this->resourceConnection->getConnection();
            $table = $this->resourceConnection->getTableName(Sitemap::ENTITY_DATA_TABLE_NAME);
            $additionalEntities = $this->getAdditionalEntities($sitemap);
            if ($sitemap->getOrigData(self::ADDITIONAL_ENTITIES) !== $sitemap->getData(self::ADDITIONAL_ENTITIES)) {
                $oldEntities = array_diff_key($sitemap->getEntitiesData() ?? [], $additionalEntities);
                $this->deleteOldEntities($oldEntities, $sitemap->getSitemapId());
            }

            foreach ($this->sourceProvider->getSourcesCodes() as $entityCode) {
                $entityData = $additionalEntities[$entityCode] ?? $sitemap->getData($entityCode);
                if ($entityData) {
                    $entityData[SitemapInterface::SITEMAP_ID] = $sitemap->getSitemapId();
                    $entityData[EntityDataInterface::ENTITY_CODE] = $entityCode;
                    $entityData[EntityDataInterface::ADDITIONAL] = $this->getSerializedAdditionalData($entityData);
                    $entityData = array_intersect_key($entityData, array_flip($this->getTableFields()));

                    $connection->insertOnDuplicate($table, $entityData);
                }
            }
        }
    }

    private function deleteOldEntities(array $oldEntities, int $sitemapId): void
    {
        if ($oldEntities) {
            $connection = $this->resourceConnection->getConnection();
            $table = $this->resourceConnection->getTableName(Sitemap::ENTITY_DATA_TABLE_NAME);
            $connection->delete(
                $table,
                [
                    $connection->prepareSqlCondition(
                        EntityDataInterface::ENTITY_CODE,
                        [
                            'in' => array_keys($oldEntities)
                        ]
                    ),
                    $connection->prepareSqlCondition(
                        SitemapInterface::SITEMAP_ID,
                        [
                            'eq' => $sitemapId
                        ]
                    )
                ]
            );
        }
    }

    private function getAdditionalEntities(SitemapInterface $sitemap): array
    {
        $additionalEntities = $sitemap->getData(self::ADDITIONAL_ENTITIES) ?: [];
        $result = [];

        foreach ($additionalEntities as $entity) {
            $result[$entity[EntityDataInterface::ENTITY_CODE]] = $entity;
            $result[$entity[EntityDataInterface::ENTITY_CODE]][EntityDataInterface::ENABLED]
                = $sitemap->isAdditionalInclude();
        }

        return $result;
    }

    private function getSerializedAdditionalData(array $data): string
    {
        $additionalData = array_diff_key($data, array_flip($this->getTableFields()));

        return $additionalData ? $this->jsonSerializer->serialize($additionalData) : '{}';
    }

    private function getTableFields(): array
    {
        if (!$this->tableFields) {
            $table = $this->resourceConnection->getTableName(Sitemap::ENTITY_DATA_TABLE_NAME);
            $fields = $this->resourceConnection->getConnection()->describeTable($table);

            $this->tableFields = array_keys($fields);
        }

        return $this->tableFields;
    }
}
