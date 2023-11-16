<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Aggregation extends AbstractDb
{
    public const MAIN_TABLE = 'amasty_shopbyfilteranalytics_aggregation';

    public const OPTION_ID = 'option_id';
    public const ATTRIBUTE_ID = 'attribute_id';
    public const COUNTER = 'counter';
    public const DATE = 'date';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, self::OPTION_ID);
    }

    /**
     * @param Select $select
     */
    public function insertFromSelect(Select $select): void
    {
        $connection = $this->getConnection();
        $connection->query(
            $connection->insertFromSelect(
                $select,
                $this->getMainTable(),
                [self::OPTION_ID, self::COUNTER, self::ATTRIBUTE_ID, self::DATE]
            )
        );
    }

    /**
     * @return Select
     */
    public function getSelect(): Select
    {
        return $this->getConnection()->select()
            ->from(['main_table' => $this->getMainTable()], [self::OPTION_ID, self::COUNTER])
            ->group([self::OPTION_ID]);
    }

    /**
     * @param Select $select
     */
    public function addAttributeColumn(Select $select): void
    {
        $select->columns([self::ATTRIBUTE_ID]);
        $select->group([self::ATTRIBUTE_ID]);
    }

    /**
     * @return Select
     */
    public function getAggregationSelect(): Select
    {
        return $this->getSelect();
    }

    /**
     * Delete collected statistics.
     */
    public function flushTable(): void
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }
}
