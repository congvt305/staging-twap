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
            $totalDiscount = 0;
            foreach ($model->getAllItems() as $item) {
                // to determine the child item discount, we calculate the parent
                if ($item->getDiscountAmount() > 0) {
                    $totalDiscount += $item->getDiscountAmount();
                }
            }
            $model->setBaseSubtotalWithDiscount($model->getBaseSubtotal() - $totalDiscount);
            $model->setSubtotalWithDiscount($model->getSubtotal() - $totalDiscount);
        }
        return [$model];
    }
}
