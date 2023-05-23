<?php

namespace CJ\Coupons\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CustomCouponRenderer
 */
class CustomCouponRenderer
{
    /**
     * @var \CJ\Coupons\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param \CJ\Coupons\Helper\Data $dataHelper
     */
    public function __construct(
        \CJ\Coupons\Helper\Data $dataHelper
    ) {
       $this->dataHelper = $dataHelper;
    }

    /**
     * @param \Amasty\Coupons\Model\CouponRenderer $subject
     * @param \Closure $proceed
     * @param $couponString
     * @return array|mixed
     */
    public function aroundParseCoupon(\Amasty\Coupons\Model\CouponRenderer $subject, \Closure $proceed, $couponString) {
        $multiCouponEnabled = $this->dataHelper->getMultiCouponEnabled();
        if ($multiCouponEnabled) {
            return $proceed($couponString);
        }

        // If setting multicoupon is set to NO, we will not split coupons string to many coupon codes
        if (!$couponString) {
            return [];
        }

        $coupons = [$couponString];
        $result = [];
        foreach ($coupons as &$coupon) {
            $coupon = trim($coupon);
            if ($coupon && $this->findCouponInArray($coupon, $result) === false) {
                $result[] = $coupon;
            }
        }

        return $result;
    }

    /**
     * @param string|null $coupon
     * @param array|null $couponArray
     *
     * @return false|int
     */
    protected function findCouponInArray(?string $coupon, ?array $couponArray) {
        if (!is_array($couponArray) || !$coupon) {
            return false;
        }
        foreach ($couponArray as $key => $code) {
            if (strcasecmp($coupon, $code) === 0) {
                return $key;
            }
        }

        return false;
    }
}
