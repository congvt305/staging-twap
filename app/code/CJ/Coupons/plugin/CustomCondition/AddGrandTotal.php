<?php
declare(strict_types=1);

namespace CJ\Coupons\plugin\CustomCondition;

/**
 * Class AddGrandTotal
 */
class AddGrandTotal
{
    /**
     * @param \Magento\SalesRule\Model\Rule\Condition\Address $subject
     * @param callable $proceed
     * @return \Magento\SalesRule\Model\Rule\Condition\Address
     */
    public function aroundLoadAttributeOptions(
        \Magento\SalesRule\Model\Rule\Condition\Address $subject,
        callable $proceed
    ): \Magento\SalesRule\Model\Rule\Condition\Address {
        $attributes = [
            'grand_total' => __('Grand Total'),
            'base_subtotal_with_discount' => __('Subtotal with discount amount'),
            'base_subtotal_total_incl_tax' => __('Subtotal (Incl. Tax)'),
            'base_subtotal' => __('Subtotal'),
            'total_qty' => __('Total Items Quantity'),
            'weight' => __('Total Weight'),
            'payment_method' => __('Payment Method'),
            'shipping_method' => __('Shipping Method'),
            'postcode' => __('Shipping Postcode'),
            'region' => __('Shipping Region'),
            'region_id' => __('Shipping State/Province'),
            'country_id' => __('Shipping Country'),
        ];

        $subject->setAttributeOption($attributes);

        return $subject;
    }
}
