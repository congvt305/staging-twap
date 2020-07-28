<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/27/20
 * Time: 3:43 PM
 */

namespace Eguana\StoreSms\Api;


interface SmsManagementInterface
{
    /**
     * @param string $number
     * @param string $message
     * @param int|null $storeId
     * @return bool
     */
    public function sendMessage(string $number, string $message, int $storeId = null): bool;

}
