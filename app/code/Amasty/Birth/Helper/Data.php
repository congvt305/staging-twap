<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Special Occasion Coupons for Magento 2
*/
namespace Amasty\Birth\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_scopeConfig;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $ruleFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    protected $groupFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\RuleFactory
     */
    protected $ruleResourceFactory;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\SalesRule\Model\ResourceModel\RuleFactory $ruleResourceFactory,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $context->getScopeConfig();
        $this->ruleFactory = $ruleFactory;
        $this->date = $date;
        $this->groupFactory = $groupFactory;
        $this->_storeManager = $storeManager;
        $this->ruleResourceFactory = $ruleResourceFactory;
    }

    public function getCurrentWebsiteId()
    {
        return $this->_storeManager->getWebsite()->getId();
    }

    public function getModuleConfig($path, $storeId = null)
    {
        if ($storeId) {
            return $this->_scopeConfig->getValue(
                'amasty_birth/' . $path,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return $this->_scopeConfig->getValue('amasty_birth/' . $path);
    }

    public function getUrl($string, $data)
    {
        return $this->_getUrl($string, $data);
    }

    public function generateCoupon($type, $store = null, $customerEmail = '')
    {
        /** @var  \Magento\SalesRule\Model\Rule $rule */
        $rule = $this->ruleFactory->create();
        $days = intVal($this->getModuleConfig($type . '/coupon_days'));

        $couponData = [
            'name' => 'Special Coupon: ' . ucfirst($type) . ' for ' . $customerEmail,
            'is_active' => 1,
            'coupon_type' => 2,
            'coupon_code' => $rule->getCouponCodeGenerator()->generateCode(),
            'stop_rules_processing' => 0,
            'uses_per_coupon' => intVal($this->getModuleConfig($type . '/coupon_uses')),
            'uses_per_customer' => intVal($this->getModuleConfig($type . '/uses_per_customer')),
            'from_date' => $this->date->date('Y-m-d'),
            'to_date' => $this->date->date('Y-m-d', strtotime("+$days days")),
            'simple_action' => $this->getModuleConfig($type . '/coupon_type'),
            'discount_amount' => $this->getModuleConfig($type . '/coupon_amount'),
            'website_ids' => array_keys($this->_storeManager->getWebsites(true))
        ];

        $couponData['conditions'] = [
            '1' => [
                'type' => 'Magento\SalesRule\Model\Rule\Condition\Combine',
                'aggregator' => 'all',
                'value' => 1,
                'new_child' => '',
                'conditions' =>
                    [
                        '1' => [
                            'type' => 'Magento\SalesRule\Model\Rule\Condition\Address',
                            'attribute' => 'base_subtotal',
                            'operator' => '>=',
                            'value' => floatVal($this->getModuleConfig($type . '/min_order')),
                        ]
                    ]
            ]
        ];

        $couponData['actions'] = [
            1 => [
                'type' => 'Magento\SalesRule\Model\Rule\Condition\Product\Combine',
                'aggregator' => 'all',
                'value' => 1,
                'new_child' => '',
            ]
        ];

        //create for all customer groups
        $couponData['customer_group_ids'] = [];
        if (!$this->getModuleConfig($type . '/customer_group')) {
            $customerGroups = $this->groupFactory->create();

            $found = false;
            foreach ($customerGroups as $group) {
                if (0 == $group->getId()) {
                    $found = true;
                }
                $couponData['customer_group_ids'][] = $group->getId();
            }
            if (!$found) {
                $couponData['customer_group_ids'][] = 0;
            }
        } else {
            $groups = $this->getModuleConfig($type . '/customer_group');
            $couponData['customer_group_ids'] = explode(',', $groups);
        }

        try {
            $rule->loadPost($couponData);
            $this->ruleResourceFactory->create()->save($rule);
        } catch (\Exception $e) {
            $couponData['coupon_code'] = '';
        }

        return $couponData['coupon_code'];

    }
}
