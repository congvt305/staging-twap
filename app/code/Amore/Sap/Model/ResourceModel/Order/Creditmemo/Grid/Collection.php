<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-08-05
 * Time: 오후 4:06
 */

namespace Amore\Sap\Model\ResourceModel\Order\Creditmemo\Grid;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Grid\Collection
{
    public function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ["sales_creditmemo" =>
        $this->_resource->getConnection()->select()->from('sales_creditmemo', ['entity_id', 'sap_creditmemo_send_check'])],
            "main_table.entity_id = sales_creditmemo.entity_id",
            [
                'sap_creditmemo_send_check'
            ]
        );
        return $this;
    }
}
