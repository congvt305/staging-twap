<?php
namespace Eguana\StoreSms\Api;

/**
 * Interface SmsInterface
 */
interface SmsInterface
{
    /**
     * Send verification code to user number
     *
     * @api
     * @param int|string $number Users name.
     * @return int|string
     */
    public function sendMessage($number);
}
