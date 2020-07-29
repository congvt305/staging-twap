<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/07/22
 * Time: 1:16 PM
 */

namespace Ecpay\Ecpaypayment\Model\Plugin\Sales\Order;

class Grid
{
    public static $table = 'sales_order_grid';
    public static $leftJoinTable = 'sales_order';

    public function afterSearch($intercepter, $collection)
    {
        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) {
            $leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);

            $collection->getSelect()->joinLeft(
                ['so' => $leftJoinTableName],
                'so.entity_id = main_table.entity_id',
                [
                    'ecpay_payment_method' => 'so.ecpay_payment_method'
                ]
            );

            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);
            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where);
        }
        return $collection;
    }
}
