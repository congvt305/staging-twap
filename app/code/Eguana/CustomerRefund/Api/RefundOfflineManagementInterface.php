<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/5/20
 * Time: 10:40 AM
 */

namespace Eguana\CustomerRefund\Api;


interface RefundOfflineManagementInterface
{
    const STATUS_PENDING_REFUND = 'pending_refund';
    /**
     * @param \Eguana\CustomerRefund\Api\Data\BankInfoDataInterface $bankInfoData
     * @return bool
     */
    public function process(\Eguana\CustomerRefund\Api\Data\BankInfoDataInterface $bankInfoData):bool;

}
