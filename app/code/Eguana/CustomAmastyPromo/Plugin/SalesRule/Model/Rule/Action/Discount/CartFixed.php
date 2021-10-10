<?php

namespace Eguana\CustomAmastyPromo\Plugin\SalesRule\Model\Rule\Action\Discount;

use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\Validator;

/**
 * Around function Calculates discount for cart item if fixed discount applied on whole cart.
 */
class CartFixed
{
    /**
     * @var \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory
     */
    protected $discountFactory;

    /**
     * @var \Magento\SalesRule\Model\Validator
     */
    protected $validator;

    /**
     * @param Validator $validator
     * @param DataFactory $discountDataFactory
     */
    public function __construct(
        Validator $validator,
        DataFactory $discountDataFactory
    )
    {
        $this->validator = $validator;
        $this->discountFactory = $discountDataFactory;
    }

    /**
     * Check $ruleTotals['base_items_price'] before calculate discount
     *
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\CartFixed $subject
     * @param \Closure $proceed
     * @param $rule
     * @param $item
     * @param $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundCalculate(
        \Magento\SalesRule\Model\Rule\Action\Discount\CartFixed $subject, \Closure $proceed, $rule, $item, $qty)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        $ruleTotals = $this->validator->getRuleItemTotalsInfo($rule->getId());
        if ($ruleTotals['base_items_price'] <= 0) {
            return $discountData;
        }

        return $proceed($rule, $item, $qty);
    }
}
