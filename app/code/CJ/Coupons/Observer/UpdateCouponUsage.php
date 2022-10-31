<?php

namespace CJ\Coupons\Observer;


use Amasty\Coupons\Model\CouponRenderer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Model\Order;
use Magento\SalesRule\Model\Coupon as CouponModel;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\ResourceModel\Coupon;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;

class UpdateCouponUsage extends \Amasty\Coupons\Observer\UpdateCouponUsage
{
    /**
     * @var Coupon
     */
    private $coupon;

    /**
     * @var Usage
     */
    private $couponUsage;

    /**
     * @var CouponRenderer
     */
    private $couponRenderer;

    /**
     * Save used coupon code ID
     *
     * @var
     */
    private $usedCodes = [];

    /**
     * @var CouponFactory
     */
    private $couponFactory;

    /**
     * Number of coupons used
     *
     * @var array
     */
    private $timesUsed = [];

    /**
     * @var \CJ\Coupons\Logger\Logger
     */
    protected $logger;

    public function __construct(
        Coupon $coupon,
        Usage $couponUsage,
        CouponRenderer $couponRenderer,
        \CJ\Coupons\Logger\Logger $logger,
        CouponFactory $couponFactory
    ) {
        $this->coupon = $coupon;
        $this->couponUsage = $couponUsage;
        $this->couponRenderer = $couponRenderer;
        $this->couponFactory = $couponFactory;
        $this->logger = $logger;
    }
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        if (!$order) {
            return $this;
        }
        // if order placement then increment else if order cancel then decrement
        $increment = (bool)($observer->getEvent()->getName() !== 'order_cancel_after');
        $placeBefore = $observer->getEvent()->getName() === 'sales_order_place_before';
        $customerId = $order->getCustomerId();
        $coupons = $this->couponRenderer->parseCoupon($order->getCouponCode());
        if (is_array($coupons) && count($coupons) > 1) {
            foreach ($coupons as $coupon) {
                if ($this->isUsed($coupon, $placeBefore)) {
                    continue;
                }
                /** @var CouponModel $couponEntity */
                $couponEntity = $this->couponFactory->create();
                $this->coupon->load($couponEntity, $coupon, 'code');

                if ($couponEntity->getId()) {
                    if (!$placeBefore) {
                        $couponEntity->setTimesUsed($this->getResultTimesUsed($couponEntity) + ($increment ? 1 : -1));
                        $this->coupon->save($couponEntity);
                        if ($customerId) {
                            $this->couponUsage->updateCustomerCouponTimesUsed(
                                $customerId,
                                $couponEntity->getId(),
                                $increment
                            );
                        }
                    } else {
                        $this->timesUsed['coupon_times_used'][$couponEntity->getId()] = $couponEntity->getTimesUsed();
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param string $code
     * @param bool $placeBefore
     *
     * @return bool
     */
    private function isUsed($code, $placeBefore)
    {
        if (!isset($this->usedCodes[$code])) {
            if (!$placeBefore) {
                $this->usedCodes[$code] = 1;
            }
            return false;
        }

        return true;
    }

    /**
     * Magento add value in column 'times_used' in DB. We also add value in column 'times_used'.
     * In this method we override this value on general solution
     *
     * @param CouponModel $couponEntity
     *
     * @return string
     */
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
