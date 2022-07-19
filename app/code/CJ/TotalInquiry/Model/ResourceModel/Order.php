<?php

namespace CJ\TotalInquiry\Model\ResourceModel;

/**
 * Class Order
 * @package CJ\TotalInquiry\Model\ResourceModel
 */
class Order extends \Magento\Sales\Model\ResourceModel\Order
{
    /**
     * @param $status
     * @param $customerId
     * @param null $orStatus
     * @return int
     */
    public function getTotalOrders($status, $customerId, $orStatus = null)
    {
        $connection = $this->getConnection();
        $salesOrderTable = $connection->getTableName('sales_order');
        $select = $connection->select()->from(
            ['main_table' => $salesOrderTable],
            [new \Zend_Db_Expr('COUNT(main_table.entity_id)')])
            ->where('main_table.customer_id = ' . $customerId . ' and main_table.status = ?', $status);
        if (!empty($orStatus)) {
            $select->orWhere('main_table.customer_id = ' . $customerId . ' and main_table.status = ?', $orStatus);
        }
        $counts = $this->getConnection()->fetchOne($select);

        return (int)$counts;
    }
}
