<?php
/**
 * @author Eguana Team
 * Created by PhpStorm
 * User: Sonia Park
 * Date: 07/25/2021
 */


namespace Amore\GcrmBanner\Api;


/**
 * Interface CouponManagerInterface
 * @api
 * @package Amore\GcrmBanner\Api
 */
interface CouponManagerInterface
{
    /**
     * @param int $customerId
     * @param string $salesruleId
     * @return mixed
     */
    public function generate($customerId, $salesruleId);

}
