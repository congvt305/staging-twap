<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 29/12/20
 * Time: 7:03 PM
 */
namespace Eguana\CustomOrderGrid\Model\ResourceModel\Grid;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as ParentCollection;

class Collection extends ParentCollection
{

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $tableDescription = $this->getConnection()->describeTable($this->getMainTable());
        foreach ($tableDescription as $columnInfo) {
            $this->addFilterToMap($columnInfo['COLUMN_NAME'], 'main_table.' . $columnInfo['COLUMN_NAME']);
        }

        $this->getSelect()->joinLeft(
            [$this->getTable('sales_shipment')],
            'main_table.entity_id = '.$this->getTable('sales_shipment').'.order_id',
            [$this->getTable('sales_shipment').'.order_id']
        );
        $this->getSelect()->joinLeft(
            [$this->getTable('inventory_shipment_source')],
            'inventory_shipment_source.shipment_id = '.$this->getTable('sales_shipment').'.entity_id',
            [$this->getTable('inventory_shipment_source').'.source_code']
        );
        $this->addFilterToMap('allocated_sources', $this->getTable('inventory_shipment_source').'.source_code');
        return $this;
    }
}
