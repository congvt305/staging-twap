<?php

namespace CJ\CouponCustomer\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'cj_couponcustomer';
    /**
     * @var \Eguana\BlackCat\Helper\Data
     */
    private $helper;

    public function __construct(
        \CJ\CouponCustomer\Helper\Data $helper){
        $this->helper = $helper;
    }

    public function getConfig()
    {
        return [
            self::CODE => [
                'coupon_list' => $this->helper->getCustomerCouponList(),
                'active_popup' => $this->helper->isEnableCouponListPopup()
            ],
        ];
    }
}
