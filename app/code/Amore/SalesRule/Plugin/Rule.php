<?php
namespace Amore\SalesRule\Plugin;

use Magento\SalesRule\Model\Rule as SalesRule;

class Rule
{
    /**
     * Before plugin needed for existing and new rules alike
     * @param SalesRule $subject
     * @return SalesRule
     */
    public function beforeSave(SalesRule $subject)
    {
        if (in_array($subject->getSimpleAction(), \Amasty\Promo\Observer\Salesrule\Discount::PROMO_RULES)) {
            $subject->setData(\Magento\SalesRule\Model\Data\Rule::KEY_SIMPLE_FREE_SHIPPING, 0);
        }
        return [];
    }
}
