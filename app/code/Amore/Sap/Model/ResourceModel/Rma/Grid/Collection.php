<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-08-10
 * Time: 오후 1:27
 */

namespace Amore\Sap\Model\ResourceModel\Rma\Grid;

class Collection extends \Magento\Rma\Model\ResourceModel\Rma\Grid\Collection
{
    public function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ["magento_rma" => $this->_resource->getConnection()->select()->from("magento_rma", ["entity_id", "sap_return_send_check", "sap_response"])],
            "main_table.entity_id = magento_rma.entity_id",
            ["sap_return_send_check", "sap_response"]
        );
        return $this;
    }
}
