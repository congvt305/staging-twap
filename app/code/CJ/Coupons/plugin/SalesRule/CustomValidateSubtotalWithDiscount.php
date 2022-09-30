<?php

namespace CJ\Coupons\plugin\SalesRule;

/**
 * Class CustomValidateSubtotalWithDiscount
 */
class CustomValidateSubtotalWithDiscount
{
    /**
     * @param \Magento\AdvancedSalesRule\Model\Rule\Condition\Address $subject
     * @param $model
     * @return array
     */
    public function beforeValidate(\Magento\AdvancedSalesRule\Model\Rule\Condition\Address $subject, $model) {
        if ($subject->getAttribute() == 'base_subtotal_with_discount') {
            $model->setBaseSubtotalWithDiscount($model->getBaseSubtotal() + $model->getDiscountAmount());
        }
        return [$model];
    }
}
