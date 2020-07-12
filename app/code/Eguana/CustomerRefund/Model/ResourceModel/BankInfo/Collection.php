<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/12/20
 * Time: 8:45 AM
 */

namespace Eguana\CustomerRefund\Model\ResourceModel\BankInfo;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Eguana\CustomerRefund\Model\BankInfo::class, \Eguana\CustomerRefund\Model\ResourceModel\BankInfo::class);
        $this->setMainTable('eguana_customerrefund_bankinfo');
    }

}
