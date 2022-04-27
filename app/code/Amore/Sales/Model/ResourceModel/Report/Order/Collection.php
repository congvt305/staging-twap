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
        $this->_selectedColumns['atv'] = '(SUM(total_income_amount) / SUM(orders_count))';
        $this->_selectedColumns['sku_value'] = '(SUM(total_income_amount) / SUM(total_qty_ordered))';
        if (!$this->isTotals()) {
            $this->_selectedColumns['net_sales'] = 'SUM(net_sales)';
            $this->_selectedColumns['total_income_amount_before_discount'] = 'SUM(total_income_amount_before_discount)';
            $this->_selectedColumns['total_refunded_amount_actual'] = 'SUM(total_refunded_amount_actual)';
        }
        return $this->_selectedColumns;
    }
}
