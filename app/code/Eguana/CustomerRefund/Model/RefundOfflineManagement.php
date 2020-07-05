<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/5/20
 * Time: 10:41 AM
 */

namespace Eguana\CustomerRefund\Model;

class RefundOfflineManagement implements \Eguana\CustomerRefund\Api\RefundOfflineManagementInterface
{
    /**
     * @param string $orderId
     * @return bool
     */
    public function process(string $orderId): bool
    {

    }
}

