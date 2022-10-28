<?php

namespace CJ\CustomAtome\Model;

use Amasty\Coupons\Model\CouponRenderer;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\ResourceModel\Coupon;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;

/**
 * Class CouponUsage
 */
class CouponUsage
{
    /**
     * @var CouponRenderer
     */
    protected $couponRenderer;

    /**
     * @var CouponFactory
     */
    protected $couponFactory;

    /**
     * @var Coupon
     */
    protected $coupon;

    /**
     * @var Usage
     */
    protected $couponUsage;

    /**
     * @var \Magento\SalesRule\Model\Rule\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @param CouponRenderer $couponRenderer
     * @param CouponFactory $couponFactory
     * @param Coupon $coupon
     * @param Usage $couponUsage
     */
    public function __construct(
        \Amasty\Coupons\Model\CouponRenderer $couponRenderer,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\ResourceModel\Coupon $coupon,
        \Magento\SalesRule\Model\ResourceModel\Coupon\Usage $couponUsage,
        \Magento\SalesRule\Model\Rule\CustomerFactory $customerFactory
    ) {
        $this->coupon = $coupon;
        $this->couponRenderer = $couponRenderer;
        $this->couponFactory = $couponFactory;
        $this->customerFactory = $customerFactory;
        $this->couponUsage = $couponUsage;
    }

    /**
     * @param $order
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function refundUsedTime($order)
    {
        $increment = false;
        $customerId = $order->getCustomerId();
        $coupons = $this->couponRenderer->parseCoupon($order->getCouponCode());
        foreach ($coupons as $coupon) {
            $couponEntity = $this->couponFactory->create();
            $this->coupon->load($couponEntity, $coupon, 'code');
            if ($couponEntity->getId()) {
                $couponEntity->setTimesUsed($this->getResultTimesUsed($couponEntity) + ($increment ? 1 : -1));
                $this->coupon->save($couponEntity);
                if ($customerId) {
                    $this->couponUsage->updateCustomerCouponTimesUsed(
                        $customerId,
                        $couponEntity->getId(),
                        $increment
                    );
                }
            }
        }
    }

    private function getResultTimesUsed($couponEntity)
    {
        if(isset($this->timesUsed['coupon_times_used'][$couponEntity->getId()])) {
            return $couponEntity->getTimesUsed() === $this->timesUsed['coupon_times_used'][$couponEntity->getId()]
                ? $couponEntity->getTimesUsed()
                : $this->timesUsed['coupon_times_used'][$couponEntity->getId()];
        }
        return $couponEntity->getTimesUsed();
    }
}
