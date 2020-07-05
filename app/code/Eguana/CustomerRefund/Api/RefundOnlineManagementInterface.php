<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/5/20
 * Time: 10:39 AM
 */

namespace Eguana\CustomerRefund\Api;


interface RefundOnlineManagementInterface
{
    /**
     * @param string $orderId
     * @return bool
     */
    public function process(string $orderId):bool;

}
