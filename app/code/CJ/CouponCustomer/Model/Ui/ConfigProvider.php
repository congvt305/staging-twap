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
        if (!$this->helper->isCustomerLogin() || !$this->helper->isEnableCouponListPopup()) {
            return [
                self::CODE => [
                    'coupon_list' => [],
                    'active_popup' => false,
                    'website_code' => $this->helper->getCurrentWebsiteCode()
                ]
            ];
        }
        return [
            self::CODE => [
                'coupon_list' => $this->helper->getCustomerCouponList(),
                'active_popup' => $this->helper->isEnableCouponListPopup(),
                'website_code' => $this->helper->getCurrentWebsiteCode()
            ],
        ];
    }
}
