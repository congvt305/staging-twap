<?php
declare(strict_types=1);
/**
 * @author Eguana Team
 * Created by PhpStorm
 * User: Sonia Park
 * Date: 07/25/2021
 */

namespace Amore\GcrmBanner\Model\Coupon;

class CouponManager implements \Amore\GcrmBanner\Api\CouponManagerInterface
{
    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $ruleFactory;
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule
     */
    private $ruleResource;
    /**
     * @var CouponGenerator
     */
    private $couponGenerator;

    public function __construct(
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\SalesRule\Model\ResourceModel\Rule $ruleResource,
        \Amore\GcrmBanner\Model\Coupon\CouponGenerator $couponGenerator
    )
    {
        $this->ruleFactory = $ruleFactory;
        $this->ruleResource = $ruleResource;
        $this->couponGenerator = $couponGenerator;
    }

    /**
     * @param int $customerId
     * @param string $salesruleId
     * @return mixed|void
     */
    public function generate($customerId, $salesruleId)
    {
         $couponCode = $this->couponGenerator->generateCoupon($customerId, $salesruleId);
        return $couponCode;
    }
}
