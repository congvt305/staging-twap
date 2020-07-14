<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/15/20
 * Time: 3:20 AM
 */

namespace Eguana\CustomerRefund\Api;


interface BankinfoManagementInterface
{
    /**
     * @param \Eguana\CustomerRefund\Api\Data\BankInfoDataInterface $bankInfoData
     * @return bool
     */
    public function process(\Eguana\CustomerRefund\Api\Data\BankInfoDataInterface $bankInfoData):bool;

}
