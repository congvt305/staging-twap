<?php

namespace CJ\CouponCustomer\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\View\Element\Template;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\GroupFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Header\Logo;
use \Magento\Directory\Model\Currency;


class Data extends AbstractHelper
{
    /**
     * xml path cron for creating new customer group from POS
     */
    const XML_PATH_CRON_JOB_CREATE_CUSTOMER_GROUP_ENABLE = 'coupon_wallet/cron/active';
    /**
     * xml path coupon list popup
     */
    const XML_PATH_COUPON_LIST_POPUP_ENABLE = 'coupon_wallet/general/popup';
    /**
     * xml path sync pos customer grade
     */
    const XML_PATH_SYNC_POS_CUSTOMER_GRADE_ENABLE = 'coupon_wallet/general/sync_pos_customer_grade';
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
     * @var Currency
     */
    private $currency;

    /**
     * @var
     */
    private $context;

    /**
     * @var GroupFactory
     */
    private $customerGroup;


    /**
     * @param Template\Context $context
     * @param array $data
     * @param RuleCollection $ruleCollection
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param Logo $logo
     */
    public function __construct(
        Context               $context,
        RuleCollection        $ruleCollection,
        Session               $customerSession,
        StoreManagerInterface $storeManager,
        Logo                  $logo,
        Currency              $currency,
        GroupFactory          $customerGroup
    ) {
        parent::__construct($context);
        $this->ruleCollection = $ruleCollection;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->logo = $logo;
        $this->currency = $currency;
        $this->customerGroup = $customerGroup;
    }

    /**
     * Get rule collection
     *
     * @return \Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    public function getCustomerAvailableCoupons()
    {
        $rules = $this->ruleCollection->create();
        $customer = $this->getCustomer();
        $websiteId = $customer->getWebsiteId();
        $rules->addWebsiteGroupDateFilter($websiteId, $customer->getGroupId())
            ->addFieldToFilter('coupon_type', \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC)
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('use_auto_generation', 0);
        return $rules;
    }

    /**
     * Get customer
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }

    /**
     * Get available coupons
     *
     * @return array
     */
    public function getCustomerCouponList()
    {
        $couponList = [];
        $customerAvailableCoupons = $this->getCustomerAvailableCoupons()->getItems();
        foreach ($customerAvailableCoupons as $coupon) {
            $couponData = [];
            $couponData['name'] = $coupon['name'];
            $couponData['code'] = $coupon['code'];
            $couponData['simple_action'] = $coupon['simple_action'];
            $couponData['to_date'] = $coupon['to_date'];
            $couponData['description'] = $coupon['description'];
            $couponData['discount_amount'] = $coupon['discount_amount'];
            $couponData['logo'] = $this->getLogo();

            $simpleActionString = $this->convertActionCouponToText($coupon['simple_action'], $coupon['discount_amount']);
            $couponData['simple_action_string'] = $simpleActionString;

            array_push($couponList, $couponData);
        }
        return $couponList;
    }

    /**
     * Get logo base on store
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo->getLogoSrc();
    }

    /**
     * Get currency code base on store
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrencyCode()
    {
        return $this->currency->getCurrencySymbol();
    }

    /**
     * Convert action coupon to text
     *
     * @param string $simpleAction
     * @param string $discountAmount
     * @return string
     */
    public function convertActionCouponToText($simpleAction, $discountAmount = '')
    {
        $simpleActionString = "";
        switch ($simpleAction) {
            case "by_percent":
                $simpleActionString = floatval($discountAmount) . "%";
                break;
            case "cart_fixed":
                $simpleActionString = $this->getCurrencyCode() . floatval($discountAmount);
                break;
            case "buy_x_get_y":
                $simpleActionString = "Buy X get Y";
                break;
            default:
                $simpleActionString = "Automatically add products to cart";
        }
        return $simpleActionString;
    }

    /**
     * Is enabled coupon list popup
     *
     * @return bool
     */
    public function isEnableCouponListPopup()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_COUPON_LIST_POPUP_ENABLE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Is enabled cronjob for creating customer group
     *
     * @return bool
     */
    public function isCronCustomerGroupEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_CRON_JOB_CREATE_CUSTOMER_GROUP_ENABLE, ScopeInterface::SCOPE_WEBSITE);
    }

    public function isPOSCustomerGradeSyncEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_SYNC_POS_CUSTOMER_GRADE_ENABLE, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * get current website code
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentWebsiteCode()
    {
        return $this->storeManager->getWebsite()->getCode();
    }

    /**
     * Check customer login
     *
     * @return bool
     */
    public function isCustomerLogin()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Get customer group Id
     *
     * @param string $name
     * @return mixed
     */
    public function getCustomerGroupIdByName($name)
    {
        $group = $this->customerGroup->create();
        $groupCollection = $group->getCollection();
        $groupCollection->addFieldToFilter('customer_group_code', $name);

        return $groupCollection->getFirstItem()->getId();
    }

    /**
     * Get all magento customer group
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getAllCustomerGroup()
    {
        $group = $this->customerGroup->create();
        $groupCollection = $group->getCollection();
        return $groupCollection->getItems();

    }

    /**
     * Is created pos customer groups for magento
     *
     * @param string $posCustomerGroup
     * @return bool
     */
    public function isCustomerGroupExist($posCustomerGroup)
    {
        $customerGroups = $this->getAllCustomerGroup();
        foreach ($customerGroups as $customerGroup) {
            if ($customerGroup->getData('customer_group_code') == $posCustomerGroup) {
                return true;
            }
        }
        return false;
    }

    /**
     * Prepare prefix for creating customer group
     *
     * @param string $customerGradeCode
     * @return false|string
     */
    public function getPrefix($customerGradeCode)
    {
        return !empty($customerGradeCode) ? substr($customerGradeCode, 0 , 3) : '';
    }


}
