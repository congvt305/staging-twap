<?php

namespace CJ\Coupons\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CustomCouponRenderer
 */
class CustomCouponRenderer
{
    const XML_PATH_MULTICOUPON_ENABLED = 'amcoupons/general/enable_multi_coupons';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @return bool
     */
    public function getMultiCouponEnabled() {
        return (bool) $this->scopeConfig->getValue(self::XML_PATH_MULTICOUPON_ENABLED, 'website');
    }

    /**
     * @param \Amasty\Coupons\Model\CouponRenderer $subject
     * @param \Closure $proceed
     * @param $couponString
     * @return array|mixed
     */
    public function aroundParseCoupon(\Amasty\Coupons\Model\CouponRenderer $subject, \Closure $proceed, $couponString) {
        $enabled = $this->getMultiCouponEnabled();
        if (!$enabled) {
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
        } else {
            return $proceed($couponString);
        }
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
