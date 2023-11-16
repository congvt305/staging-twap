<?php

namespace Amasty\CPS\Model\ResourceModel;

use Amasty\CPS\Api\Data\BrandProductInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class BrandProduct extends AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(BrandProductInterface::MAIN_TABLE, BrandProductInterface::AMBRAND_ID);
    }

    /**
     * @param int $brandId
     * @para int $storeId
     * @param array $productPositionData
     * @param array $pinnedProductIds
     */
    public function updateProductPositionsByBrand($brandId, $storeId, $productPositionData = [], $pinnedProductIds = [])
    {
        $insertData = [];
        foreach ($productPositionData as $productId => $position) {
            $insertData[] = [
                BrandProductInterface::AMBRAND_ID => $brandId,
                BrandProductInterface::PRODUCT_ID => $productId,
                BrandProductInterface::STORE_ID => $storeId,
                BrandProductInterface::POSITION => $position,
                BrandProductInterface::IS_PINNED => in_array($productId, $pinnedProductIds)
            ];
        }

        if ($insertData) {
            $this->getConnection()->insertOnDuplicate(
                $this->getTable(BrandProductInterface::MAIN_TABLE),
                $insertData,
                [BrandProductInterface::POSITION, BrandProductInterface::IS_PINNED]
            );
        }
    }

    /**
     * @param array $condition
     * @return $this
     */
    public function clearBrandData($condition = [])
    {
        $where = [];
        if ($condition) {
            if (isset($condition['products']) && $condition['products']) {
                $where = [BrandProductInterface::PRODUCT_ID . ' IN (?)' => $condition['products']];
            }

            if (isset($condition['stores']) && $condition['stores']) {
                $where = array_merge($where, [BrandProductInterface::STORE_ID . ' IN (?)' => $condition['stores']]);
            }

            if (isset($condition['brands']) && $condition['brands']) {
                $where = array_merge($where, [BrandProductInterface::AMBRAND_ID . ' IN (?)' => $condition['brands']]);
            }
        }

        $this->getConnection()->delete(
            $this->getTable($this->getMainTable()),
            $where
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function truncateMainTable()
    {
        $this->getConnection()->truncateTable($this->getTable($this->getMainTable()));
        return $this;
    }

    /**
     * @param string|array $brandId
     * @param int $storeId
     * @param bool $pinned
     * @return array
     */
    public function getProductPositionData($brandId, $storeId, $pinned = false)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable($this->getMainTable()),
            [BrandProductInterface::PRODUCT_ID, BrandProductInterface::POSITION]
        )->where('ambrand_id IN (?)', $brandId)
            ->where('store_id = ?', $storeId);
        if ($pinned) {
            $select->where('is_pinned = 1');
        }

        return $connection->fetchPairs($select->order('position'));
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param string|int $brandValue
     * @param string|int $store
     */
    public function filterByBrand($collection, $brandValue, $store)
    {
        $collection->joinTable(
            ['amasty_brand_product' => $this->getTable($this->getMainTable())],
            'product_id = entity_id',
            [
                'position' => 'position',
                'is_manual' => 'is_pinned',
                'store_id' => 'store_id'
            ],
            [
                'ambrand_id' => $brandValue,
                'store_id' => $store
            ]
        );

        return $collection;
    }

    /**
     * @param $productIds
     * @param null $storeId
     * @param null $brandId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBrandIdsByProductIds($productIds, $storeId = null, $brandId = null)
    {
        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['main_table' => $this->getTable($this->getMainTable())],
            [
                'product_id',
                'ambrand_ids'=> new \Zend_Db_Expr("GROUP_CONCAT(ambrand_id separator ',')"),
                'positions'=> new \Zend_Db_Expr("GROUP_CONCAT(position separator ',')")
            ]
        )->where('store_id = ?', $storeId)
            ->where('product_id IN (?)', $productIds)
            ->group('product_id');

        if ($brandId !== null) {
            $select->where('ambrand_id = ?', $brandId);
        }

        $productsPositionData = $connection->fetchAll($select);

        $positionData = [];
        foreach ($productsPositionData as $data) {
            $positionData[$data['product_id']] = array_combine(
                explode(',', $data['ambrand_ids']),
                explode(',', $data['positions'])
            );
        }

        return $positionData;
    }
}
