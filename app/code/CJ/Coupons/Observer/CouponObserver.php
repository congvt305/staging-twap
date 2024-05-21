<?php
declare(strict_types=1);

namespace CJ\Coupons\Observer;

use Amasty\Coupons\Model\CouponRenderer;
use Amasty\Coupons\Model\DiscountCollector;
use Amasty\Coupons\Model\IsAllowSameRuleCouponResolver;
use Amasty\Coupons\Model\SalesRule\CouponListProvider;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Model\Rule as SalesRule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data as DiscountData;

class CouponObserver implements \Magento\Framework\Event\ObserverInterface
{
    const STORE_TW_CODE = [
        'tw_laneige',
        'default'
    ];
    /**
     * @var CouponRenderer
     */
    private $couponRenderer;

    /**
     * @var CouponListProvider
     */
    private $couponListProvider;

    /**
     * @var IsAllowSameRuleCouponResolver
     */
    private $isAllowSameRuleCouponResolver;

    /**
     * @var DiscountCollector
     */
    private $discountCollector;

    /**
     * @param DiscountCollector $discountCollector
     * @param CouponRenderer $couponRenderer
     * @param IsAllowSameRuleCouponResolver $isAllowSameRuleCouponResolver
     * @param CouponListProvider $couponListProvider
     */
    public function __construct(
        DiscountCollector $discountCollector,
        CouponRenderer $couponRenderer,
        IsAllowSameRuleCouponResolver $isAllowSameRuleCouponResolver,
        CouponListProvider $couponListProvider
    ) {
        $this->discountCollector = $discountCollector;
        $this->couponRenderer = $couponRenderer;
        $this->couponListProvider = $couponListProvider;
        $this->isAllowSameRuleCouponResolver = $isAllowSameRuleCouponResolver;
    }

    /**
     * Override class Amasty\Coupons\Observer\CouponObserver
     *
     * @param Observer $observer
     *
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /** @var SalesRule $rule */
        $rule = $observer->getEvent()->getRule();
        if ($rule->getCouponType() == SalesRule::COUPON_TYPE_NO_COUPON) {
            return;
        }
        /**
         * @var  Quote $quote
         */
        $quote = $observer->getData('quote');
        /** @var DiscountData $discountData */
        $discountData = $observer->getData('result');
        $appliedCodes = $this->couponRenderer->render($quote->getCouponCode());

        //start customize
        // fix error when input coupon code and click apply
        // they apply 2 rule(1 is auto, 1 is coupoon code), rule no coupon apply first, it remove couponCode in quote
        if ($rule->getCouponCode() && in_array($quote->getStore()->getCode(), self::STORE_TW_CODE)) {
            $appliedCodes[] = $rule->getCouponCode();
        }
        //end customize

        $discount = $baseDiscount = 0;
        $amount = $discountData->getAmount();
        $baseAmount = $discountData->getBaseAmount();
        $couponItems = $this->couponListProvider->getItemsByCodes($appliedCodes);
        foreach ($couponItems as $couponRule) {
            if ($rule->getRuleId() == $couponRule->getRuleId()) {
                $this->discountCollector->applyRuleAmount($couponRule->getCode(), $amount);
                $discount += $amount;
                $baseDiscount += $baseAmount;
            }
        }

        if ($this->isAllowSameRuleCouponResolver->isAllowedForSalesRule($rule)) {
            /** @var Address $address */
            $address = $observer->getAddress();
            $availableShippingDiscountAmount = $discount - $address->getSubtotal();

            if ($availableShippingDiscountAmount > 0) {
                $cartRules = $address->getCartFixedRules();
                $cartRules[$rule->getRuleId()] = $availableShippingDiscountAmount;
                $address->setCartFixedRules($cartRules);
            }

            $discountData->setAmount($discount);
            $discountData->setBaseAmount($baseDiscount);
        }
    }
}
