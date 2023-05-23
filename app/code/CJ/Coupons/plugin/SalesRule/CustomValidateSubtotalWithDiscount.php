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
    public function beforeValidate(\Magento\AdvancedSalesRule\Model\Rule\Condition\Address $subject, $model)
    {
        if ($subject->getAttribute() == 'base_subtotal_with_discount' || $subject->getAttribute() == 'grand_total') {
            $totalDiscount = 0;
            foreach ($model->getAllItems() as $item) {
                // to determine the child item discount, we calculate the parent
                if ($item->getDiscountAmount() > 0) {
                    $totalDiscount += $item->getDiscountAmount();
                } elseif ($item->getDiscountPercent() > 0) {
                    $totalDiscount += ($item->getDiscountPercent() / 100 * $item->getPrice());
                }
            }
            if ($subject->getAttribute() == 'base_subtotal_with_discount') {
                $model->setBaseSubtotalWithDiscount($model->getBaseSubtotal() - $totalDiscount);
                $model->setSubtotalWithDiscount($model->getSubtotal() - $totalDiscount);
            } else {
                $model->setBaseGrandTotal($model->getBaseSubtotal() - $totalDiscount + $model->getShippingAmount());
                $model->setGrandTotal($model->getSubtotal() - $totalDiscount + $model->getShippingAmount());
            }

        }
        return [$model];
    }
}
