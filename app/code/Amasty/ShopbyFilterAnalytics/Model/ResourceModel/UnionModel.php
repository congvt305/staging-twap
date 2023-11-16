<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Model\ResourceModel;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

/**
 * Model of Union select of Analytics tables.
 * Resolve parts of selects
 */
class UnionModel
{
    public const OPTION_ID = 'option_id';
    public const ATTRIBUTE_ID = 'attribute_id';
    public const COUNTER = 'counter';
    public const DATE = 'date';

    /**
     * @var Select
     */
    private $tmpSelect;

    /**
     * @var Select
     */
    private $aggregatedSelect;

    /**
     * @var Select
     */
    private $unionSelect;

    /**
     * @var TmpAnalytics
     */
    private $tmpAnalytics;

    /**
     * @var Aggregation
     */
    private $aggregatedAnalytics;

    public function __construct(
        TmpAnalytics $tmpAnalytics,
        Aggregation $aggregatedAnalytics
    ) {
        $this->tmpAnalytics = $tmpAnalytics;
        $this->aggregatedAnalytics = $aggregatedAnalytics;

        $this->init();
    }

    /**
     * Initialize selects
     */
    protected function init(): void
    {
        $this->tmpSelect = $this->tmpAnalytics->getSelect();
        $this->aggregatedSelect = $this->aggregatedAnalytics->getSelect();
    }

    /**
     * @return Select
     */
    public function getSelect(): Select
    {
        if (!$this->unionSelect) {
            $this->unionSelect = $this->getConnection()
                ->select()
                ->union(
                    [
                        $this->tmpSelect,
                        $this->aggregatedSelect
                    ]
                );
        }

        return $this->unionSelect;
    }

    /**
     * Perform attribute column in option statistics
     */
    public function addAttributeColumn(): void
    {
        $this->tmpAnalytics->joinAttribute($this->tmpSelect);
        $this->aggregatedAnalytics->addAttributeColumn($this->aggregatedSelect);
    }

    /**
     * @param int|string|array $condition expected in format array("from" => $fromValue, "to" => $toValue)
     */
    public function dateFilter($condition): void
    {
        $this->addConditionToSelect($this->tmpSelect, TmpAnalytics::CREATED_AT, $condition);
        $this->addConditionToSelect($this->aggregatedSelect, Aggregation::DATE, $condition);
    }

    /**
     * @param Select $select
     * @param string $field
     * @param int|string|array $condition expected in format array("from" => $fromValue, "to" => $toValue)
     */
    private function addConditionToSelect(Select $select, string $field, $condition): void
    {
        $connection = $this->getConnection();

        $tmpCondition = $connection->prepareSqlCondition(
            $connection->quoteIdentifier($field),
            $condition
        );
        $select->where($tmpCondition);
    }

    /**
     * Get DB connection adapter.
     *
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        return $this->aggregatedAnalytics->getConnection();
    }
}
