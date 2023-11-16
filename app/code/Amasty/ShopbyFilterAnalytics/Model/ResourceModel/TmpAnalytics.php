<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class TmpAnalytics extends AbstractDb
{
    public const MAIN_TABLE = 'amasty_shopbyfilteranalytics_tmp';

    public const OPTION_ID = 'option_id';
    public const FILTER_SESSION = 'filter_session';
    public const SESSION_ID = 'session_id';
    public const CATEGORY_ID = 'category_id';
    public const STORE_ID = 'store_id';
    public const CREATED_AT = 'created_at';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'id');
    }

    /**
     * Save statistics.
     *
     * @param array $items
     */
    public function insertItems(array $items): void
    {
        $this->getConnection()->insertOnDuplicate($this->getMainTable(), $items);
    }

    /**
     * @return Select
     */
    public function getAggregationSelect(): Select
    {
        $select = $this->getSelect();
        $this->prepareToAggregation($select);

        return $select;
    }

    /**
     * @return Select
     */
    public function getSelect(): Select
    {
        return $this->getConnection()->select()
            ->from(
                ['main_table' => $this->getMainTable()],
                [
                    UnionModel::OPTION_ID => sprintf('main_table.%s', self::OPTION_ID),
                    UnionModel::COUNTER => 'COUNT(*)'
                ]
            )
            ->group('main_table.option_id');
    }

    /**
     * @param Select $select
     */
    public function prepareToAggregation(Select $select): void
    {
        $this->joinAttribute($select);

        $select->columns([UnionModel::DATE => 'DATE(created_at)']);
        $select->group('DATE(created_at)');
    }

    /**
     * Add attribute ID data.
     *
     * @param Select $select
     */
    public function joinAttribute(Select $select): void
    {
        $select->joinInner(
            ['attribute_option' => $this->getTable('eav_attribute_option')],
            'main_table.option_id = attribute_option.option_id',
            [UnionModel::ATTRIBUTE_ID => 'attribute_id']
        );
    }

    /**
     * @return array
     */
    public function getAggregationData(): array
    {
        return $this->getConnection()->fetchAll($this->getAggregationSelect());
    }

    /**
     * @param string $where
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteOldData(string $where): void
    {
        $this->getConnection()->delete($this->getMainTable(), $where);
    }

    /**
     * Delete collected statistics.
     */
    public function flushTable(): void
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }
}
