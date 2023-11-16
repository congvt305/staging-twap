<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Model\ResourceModel\Analytics;

use Amasty\ShopbyFilterAnalytics\Model\ResourceModel\Analytics;
use Amasty\ShopbyFilterAnalytics\Model\ResourceModel\UnionModel;
use Magento\Framework\DataObject;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Emulate collection with aggregated attributes options analytics.
 *
 * @method Analytics getResource()
 */
class OptionsCollection extends AbstractCollection
{
    /**
     * @var array
     */
    protected $_map = [
        'fields' => [
            'attribute_id' => 'main_table.attribute_id'
        ]
    ];

    /**
     * @var UnionModel
     */
    private $unionModel;

    protected function _construct()
    {
        $this->_init(DataObject::class, Analytics::class);
        parent::_construct();
        $this->_setIdFieldName('option_id');
    }

    protected function _initSelect()
    {
        $this->unionModel = $this->getResource()->createUnion();
        $this->unionModel->addAttributeColumn();

        $this->_select
            ->from(
                ['main_table' => $this->unionModel->getSelect()],
                ['option_id', 'counter' => 'SUM(counter)', 'attribute_id']
            )
            ->group('main_table.option_id')
            ->joinInner(
                ['option' => $this->getTable('eav_attribute_option_value')],
                'main_table.option_id = option.option_id',
                ['frontend_label' => 'value']
            )->joinInner(
                ['optionSort' => $this->getTable('eav_attribute_option')],
                'main_table.option_id = optionSort.option_id',
                []
            );

        return $this;
    }

    /**
     * Collect analytic counter in chosen date range
     *
     * @param null|string|array $condition
     */
    public function filterAnalyticsByDate($condition): void
    {
        $this->unionModel->dateFilter($condition);
    }

    /**
     * @param int[] $attributeIds
     */
    public function addAttributeIdsFilter(array $attributeIds): void
    {
        $this->addFieldToFilter('attribute_id', ['in' => $attributeIds]);
    }

    /**
     * @param array|string $field
     * @param null|string|array $condition
     *
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null): self
    {
        if ($field === 'date') {
            $this->filterAnalyticsByDate($condition);

            return $this;
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
