<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Model\ResourceModel\Analytics;

use Amasty\ShopbyFilterAnalytics\Model\ResourceModel\Analytics;
use Amasty\ShopbyFilterAnalytics\Model\ResourceModel\UnionModel;
use Magento\Framework\DataObject;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Emulate collection with aggregated attributes analytics.
 *
 * @method Analytics getResource()
 */
class FilterCollection extends AbstractCollection
{
    /**
     * @var UnionModel
     */
    private $unionModel;

    protected function _construct()
    {
        $this->_init(DataObject::class, Analytics::class);
        parent::_construct();
        $this->_setIdFieldName('attribute_id');
    }

    protected function _initSelect()
    {
        $this->unionModel = $this->getResource()->createUnion();
        $this->unionModel->addAttributeColumn();

        $this->_select
            ->from(['main_table' => $this->unionModel->getSelect()], ['attribute_id', 'counter' => 'SUM(counter)'])
            ->group('main_table.attribute_id')
            ->joinInner(
                ['eav' => $this->getTable('eav_attribute')],
                'main_table.attribute_id = eav.attribute_id',
                ['frontend_label', 'attribute_code']
            )->joinInner(
                ['additional_table' => $this->getTable('catalog_eav_attribute')],
                'additional_table.attribute_id = main_table.attribute_id',
                []
            )->where('additional_table.is_filterable > 0');

        return $this;
    }

    /**
     * Collect analytic counter in chosen date range
     *
     * @param int|string|array $condition
     */
    public function filterAnalyticsByDate($condition): void
    {
        $this->unionModel->dateFilter($condition);
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
