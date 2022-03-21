<?php
declare(strict_types=1);

namespace Amore\Sales\Model\ResourceModel\Report\Order;

class Collection extends \Magento\Sales\Model\ResourceModel\Report\Order\Collection
{
    /**
     * Get selected columns
     *
     * @return array
     */
    protected function _getSelectedColumns()
    {
        parent::_getSelectedColumns();

        $this->_selectedColumns['discount_rate'] = '(SUM(total_discount_amount) / SUM(total_income_amount + total_discount_amount) * 100) as discount_rate';
        if (!$this->isTotals()) {
            $this->_selectedColumns['atv'] = 'SUM(atv)';
            $this->_selectedColumns['sku_value'] = 'SUM(sku_value)';
        }
        return $this->_selectedColumns;
    }
}
