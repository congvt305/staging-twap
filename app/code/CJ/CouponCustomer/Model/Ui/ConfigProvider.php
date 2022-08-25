<?php

namespace CJ\CouponCustomer\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * Constant
     */
    const CODE = 'cj_couponcustomer';
    /**
     * @var \CJ\CouponCustomer\Helper\Data
     */
    private $helper;

    public function __construct(
        \CJ\CouponCustomer\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @return array[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        // for guest checkout
        // if customer is guest, we have to check if admin enable member_only mode or not?
        // if admin enable member_only, guest can't view couponList.
        $config = [
            self::CODE => [
                'coupon_list' => $this->helper->getCustomerCouponList(),
                'active_popup' => $this->helper->isEnableCouponListPopup(),
                'website_code' => $this->helper->getCurrentWebsiteCode(),
                'can_view_coupon_list' => true
            ],
        ];
        if (!$this->helper->isCustomerLogin() || !$this->helper->isEnableCouponListPopup()) {
            if ($this->helper->isMemberOnlyEnabled()) {
                $config[self::CODE]['can_view_coupon_list'] = false;
            }
        }
        return $config;
    }
}
