<?php

namespace Amore\SalesRule\Override\Model;

/**
 * Override Rule applier model
 */
class RulesApplier extends \Magento\SalesRule\Model\RulesApplier
{
    /**
     * Set Discount data and round without decimal
     *
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData
     * @param AbstractItem $item
     * @return $this
     */
    protected function setDiscountData($discountData, $item)
    {
        $item->setDiscountAmount(round($discountData->getAmount()));
        $item->setBaseDiscountAmount(round($discountData->getBaseAmount()));
        $item->setOriginalDiscountAmount(round($discountData->getOriginalAmount()));
        $item->setBaseOriginalDiscountAmount(round($discountData->getBaseOriginalAmount()));

        return $this;
    }
}
