<?php
declare(strict_types=1);

namespace CJ\AmastyBirth\Helper;

class Data extends \Amasty\Birth\Helper\Data
{
    /**
     * Customize add storeId when get config
     *
     * @param $type
     * @param $store
     * @param $customerEmail
     * @return mixed|string
     */
    public function generateCoupon($type, $store = null, $customerEmail = '')
    {
        /** @var  \Magento\SalesRule\Model\Rule $rule */
        $rule = $this->ruleFactory->create();
        $storeId = $store->getId();
        $days = intVal($this->getModuleConfig($type . '/coupon_days', $storeId));

        $couponData = [
            'name' => 'Special Coupon: ' . ucfirst($type) . ' for ' . $customerEmail,
            'is_active' => 1,
            'coupon_type' => 2,
            'coupon_code' => $rule->getCouponCodeGenerator()->generateCode(),
            'stop_rules_processing' => 0,
            'uses_per_coupon' => intVal($this->getModuleConfig($type . '/coupon_uses', $storeId)),
            'uses_per_customer' => intVal($this->getModuleConfig($type . '/uses_per_customer', $storeId)),
            'from_date' => $this->date->date('Y-m-d'),
            'to_date' => $this->date->date('Y-m-d', strtotime("+$days days")),
            'simple_action' => $this->getModuleConfig($type . '/coupon_type', $storeId),
            'discount_amount' => $this->getModuleConfig($type . '/coupon_amount', $storeId),
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
                            'value' => floatVal($this->getModuleConfig($type . '/min_order', $storeId)),
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
        if (!$this->getModuleConfig($type . '/customer_group', $storeId)) {
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
            $groups = $this->getModuleConfig($type . '/customer_group', $storeId);
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
