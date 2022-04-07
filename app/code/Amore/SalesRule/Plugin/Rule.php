<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amore\SalesRule\Plugin;

use Magento\SalesRule\Model\Rule as SalesRule;

class Rule
{
    /**
     * Around plugin needed for existing and new rules alike
     * @param SalesRule $subject
     * @param \Closure $proceed
     * @return SalesRule
     */
    public function aroundSave(SalesRule $subject, \Closure $proceed)
    {
        if (in_array($subject->getSimpleAction(), \Amasty\Promo\Observer\Salesrule\Discount::PROMO_RULES)) {
            $subject->setData(\Magento\SalesRule\Model\Data\Rule::KEY_SIMPLE_FREE_SHIPPING, 0);
        }
        return $proceed();
    }
}
