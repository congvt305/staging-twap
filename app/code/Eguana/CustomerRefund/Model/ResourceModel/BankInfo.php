<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/12/20
 * Time: 8:19 AM
 */

namespace Eguana\CustomerRefund\Model\ResourceModel;


class BankInfo extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eguana_customerrefund_bankinfo', \Eguana\CustomerRefund\Model\BankInfo::BANKINFO_ID);
    }


}
