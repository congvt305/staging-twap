<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Model\ResourceModel;

use Amasty\Shopby\Model\Source\SortOptionsBy;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class UpdatePosition
{
    /**
     * @var string
     */
    private $optionTable;

    /**
     * @var string
     */
    private $catalogTable;

    /**
     * @var string
     */
    private $filterTable;

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Save attribute option sort order.
     *
     * @param int $optionId
     * @param int $position
     */
    public function updateOption(int $optionId, int $position): void
    {
        $this->getConnection()->update(
            $this->getOptionTable(),
            [AttributeOptionInterface::SORT_ORDER => $position],
            sprintf('option_id = %s', $optionId)
        );
    }

    /**
     * @return string
     */
    public function getOptionTable(): string
    {
        if ($this->optionTable === null) {
            $this->optionTable = $this->resource->getTableName('eav_attribute_option');
        }

        return $this->optionTable;
    }

    /**
     * Save attribute positions.
     *
     * @param int $attributeId
     * @param int $position
     * @param int|null $settingId
     */
    public function updateAttribute(int $attributeId, int $position, ?int $settingId = null): void
    {
        $connection = $this->getConnection();
        $connection->update(
            $this->getCatalogAttributeTable(),
            ['position' => $position],
            sprintf('attribute_id = %d', $attributeId)
        );

        if ($settingId !== null) {
            $connection->update(
                $this->getFilterTable(),
                ['side_position' => $position, 'top_position' => $position],
                sprintf('setting_id = %d', $settingId)
            );
        }
    }

    /**
     * Save attributes property "Sort Options By" to position sorting.
     *
     * @param int[] $attributeIds
     *
     * @return void
     */
    public function changeAttributesSortBy(array $attributeIds): void
    {
        $connection = $this->getConnection();

        $attributeCodes = $this->getAttributeCodesByIds($attributeIds);

        $connection->update(
            $this->getFilterTable(),
            [FilterSettingInterface::SORT_OPTIONS_BY => SortOptionsBy::POSITION],
            [FilterSettingInterface::ATTRIBUTE_CODE . ' IN (?)' => $attributeCodes]
        );
    }

    /**
     * @return string
     */
    public function getCatalogAttributeTable(): string
    {
        if ($this->catalogTable === null) {
            $this->catalogTable = $this->resource->getTableName('catalog_eav_attribute');
        }

        return $this->catalogTable;
    }

    /**
     * @return string
     */
    public function getFilterTable(): string
    {
        if ($this->filterTable === null) {
            $this->filterTable = $this->resource->getTableName(FilterSettingRepositoryInterface::TABLE);
        }

        return $this->filterTable;
    }

    /**
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        return $this->resource->getConnection();
    }

    /**
     * @param int[] $attributeIds
     *
     * @return string[]
     */
    private function getAttributeCodesByIds(array $attributeIds): array
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->resource->getTableName('eav_attribute'),
            [AttributeInterface::ATTRIBUTE_CODE]
        )->where(AttributeInterface::ATTRIBUTE_ID . ' IN (?)', $attributeIds);

        return $connection->fetchCol($select);
    }
}
