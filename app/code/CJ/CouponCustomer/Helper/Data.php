<?php

namespace CJ\CouponCustomer\Helper;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Header\Logo;
use Magento\Theme\Block\Html\Pager;


class Data
{
    /**
     * @var RuleCollection
     */
    private $ruleCollection;
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Logo
     */
    private $logo;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;


    /**
     * @param Template\Context $context
     * @param array $data
     * @param RuleCollection $ruleCollection
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param Logo $logo
     */
    public function __construct(
        RuleCollection        $ruleCollection,
        Session               $customerSession,
        StoreManagerInterface $storeManager,
        Logo                  $logo
    ){
        $this->ruleCollection = $ruleCollection;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->logo = $logo;
    }

    public function getRuleCollection()
    {
//        return [
//            ['coupon_id' => 123123,
//                'copun_des'=> "thanhdatr12312312"],
//            [
//                'coupon_id' => 222222,
//                'copun_des'=> "test12312312"
//            ]
//        ];
        $rules = $this->ruleCollection->create();
        $customer = $this->getCustomer();
        $websiteId = $customer->getWebsiteId();
        $rules->addWebsiteGroupDateFilter($websiteId, $customer->getGroupId())
            ->addFieldToFilter('coupon_type', \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC)
            ->addFieldToFilter('is_active', 1);
        return $rules;
    }
    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }

    public function getCouponList() {
        $couponArray = [];
        $couponList = $this->getRuleCollection()->getItems();
        foreach ($couponList as $coupon) {
            $couponData = [];
            $couponData['name'] = $coupon['name'];
            $couponData['code'] = $coupon['code'];
            $couponData['simple_action'] = $coupon['simple_action'];
            $couponData['to_date'] = $coupon['to_date'];
            $couponData['description'] = $coupon['description'];
            $couponData['discount_amount'] = $coupon['discount_amount'];

            $simpleActionString = $this->convertActionCouponToText($coupon['simple_action'], $coupon['discount_amount']);
            $couponData['simple_action_string'] = $simpleActionString;

            array_push($couponArray, $couponData);
        }
        return $couponArray;
    }

    public function convertActionCouponToText($simpleAction, $discountAmount = '') {
        $simpleActionString = "";
        switch ($simpleAction) {
            case "by_percent":
                $simpleActionString = floatval($discountAmount) . "%";
                break;
            case "cart_fixed":
                $simpleActionString = floatval($discountAmount) . "NT$";
                break;
            case "buy_x_get_y":
                $simpleActionString = "Buy X get Y";
                break;
            default:
                $simpleActionString = "Automatically add products to cart";
        }
        return $simpleActionString;
    }


}
