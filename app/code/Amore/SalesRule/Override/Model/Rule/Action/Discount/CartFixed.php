<?php

namespace Amore\SalesRule\Override\Model\Rule\Action\Discount;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Helper\CartFixedDiscount;
use Magento\SalesRule\Model\DeltaPriceRound;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\Validator;
use CJ\CustomSales\Helper\Data as Helper;

class CartFixed extends \Magento\SalesRule\Model\Rule\Action\Discount\CartFixed
{
    /**
     * @var string
     */
    private static $discountType = 'CartFixed';

    /**
     * @var DeltaPriceRound
     */
    private $deltaPriceRound;

    /**
     * @var CartFixedDiscount|mixed
     */
    private $cartFixedDiscountHelper;

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    protected $promoItemHelper;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Validator $validator
     * @param DataFactory $discountDataFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param DeltaPriceRound $deltaPriceRound
     * @param \Amasty\Promo\Helper\Item $promoItemHelper
     * @param CartFixedDiscount|null $cartFixedDiscount
     */
    public function __construct(
        Validator $validator,
        DataFactory $discountDataFactory,
        PriceCurrencyInterface $priceCurrency,
        DeltaPriceRound $deltaPriceRound,
        \Amasty\Promo\Helper\Item $promoItemHelper,
        Helper $helper,
        ?CartFixedDiscount $cartFixedDiscount = null
    ) {
        $this->deltaPriceRound = $deltaPriceRound;
        $this->helper = $helper;
        $this->cartFixedDiscountHelper = $cartFixedDiscount ?:
            ObjectManager::getInstance()->get(CartFixedDiscount::class);
        $this->promoItemHelper = $promoItemHelper;
        parent::__construct(
            $validator,
            $discountDataFactory,
            $priceCurrency,
            $deltaPriceRound,
            $cartFixedDiscount
        );
    }

    /**
     * Fixed discount for cart calculation
     *
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     * @return Data
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function calculate($rule, $item, $qty)
    {
        /** @var Data $discountData */
        $discountData = $this->discountFactory->create();

        $ruleTotals = $this->validator->getRuleItemTotalsInfo($rule->getId());
        $baseRuleTotals = $ruleTotals['base_items_price'] ?? 0.0;
        $baseRuleTotalsDiscount = $ruleTotals['base_items_discount_amount'] ?? 0.0;
        $ruleItemsCount = $ruleTotals['items_count'] ?? 0;

        $address = $item->getAddress();
        $quote = $item->getQuote();
        $shippingMethod = $address->getShippingMethod();
        $isAppliedToShipping = (int)$rule->getApplyToShipping();
        $ruleDiscount = (float)$rule->getDiscountAmount();

        $isMultiShipping = $this->cartFixedDiscountHelper->checkMultiShippingQuote($quote);
        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);
        $baseItemDiscountAmount = (float)$item->getBaseDiscountAmount();

        $cartRules = $quote->getCartFixedRules();
        if (!isset($cartRules[$rule->getId()])) {
            $cartRules[$rule->getId()] = $rule->getDiscountAmount();
        }
        $availableDiscountAmount = (float)$cartRules[$rule->getId()];
        $discountType = self::$discountType . $rule->getId();

        if ($availableDiscountAmount > 0) {
            $store = $quote->getStore();
            //no need to set discount for promo item - it will be set in observer salesrule_validator_process
            if ($this->promoItemHelper->isPromoItem($item)) {
                $discountData->setAmount(0);
                $discountData->setBaseAmount(0);
                $discountData->setOriginalAmount(0);
                $discountData->setBaseOriginalAmount(0);
                $quote->setCartFixedRules($cartRules);
                if ($ruleTotals['items_count'] > 1) {
                    $this->validator->decrementRuleItemTotalsCount($rule->getId());
                }
                return $discountData;
            }
            $shippingPrice = $this->cartFixedDiscountHelper->applyDiscountOnPricesIncludedTax()
                ? (float)$address->getShippingInclTax()
                : (float)$address->getShippingExclTax();
            $baseRuleTotals = $shippingMethod ?
                $this->cartFixedDiscountHelper
                    ->getBaseRuleTotals(
                        $isAppliedToShipping,
                        $quote,
                        $isMultiShipping,
                        $address,
                        $baseRuleTotals,
                        $shippingPrice
                    ) : $baseRuleTotals;
            foreach ($address->getAllVisibleItems() as $_item) {
                if ($this->promoItemHelper->isPromoItem($_item)) {
                    $baseRuleTotals -= $_item->getRowTotal();
                    $baseRuleTotalsDiscount -= $_item->getRowTotal();
                }
            }
            if ($isAppliedToShipping) {
                $baseDiscountAmount = $this->cartFixedDiscountHelper
                    ->getDiscountAmount(
                        $ruleDiscount,
                        $qty,
                        $baseItemPrice,
                        $baseRuleTotals,
                        $discountType
                    );
            } else {
                $baseDiscountAmount = $this->cartFixedDiscountHelper
                    ->getDiscountedAmountProportionally(
                        $ruleDiscount,
                        $qty,
                        $baseItemPrice,
                        $baseItemDiscountAmount,
                        $baseRuleTotals - $baseRuleTotalsDiscount,
                        $discountType
                    );
            }
            $discountAmount = $this->priceCurrency->convert($baseDiscountAmount, $store);
            $baseDiscountAmount = min($baseItemPrice * $qty, $baseDiscountAmount);
            if ($ruleItemsCount <= 1) {
                $this->deltaPriceRound->reset($discountType);
            } else {
                $this->validator->decrementRuleItemTotalsCount($rule->getId());
            }

            $baseDiscountAmount = $this->priceCurrency->roundPrice($baseDiscountAmount);

            $availableDiscountAmount = $this->cartFixedDiscountHelper
                ->getAvailableDiscountAmount(
                    $rule,
                    $quote,
                    $isMultiShipping,
                    $cartRules,
                    $baseDiscountAmount,
                    $availableDiscountAmount
                );
            $cartRules[$rule->getId()] = $availableDiscountAmount;
            if ($isAppliedToShipping &&
                $isMultiShipping &&
                $ruleTotals['items_count'] <= 1) {
                $estimatedShippingAmount = (float)$address->getBaseShippingInclTax();
                $shippingDiscountAmount = $this->cartFixedDiscountHelper->
                getShippingDiscountAmount(
                    $rule,
                    $estimatedShippingAmount,
                    $baseRuleTotals
                );
                $cartRules[$rule->getId()] -= $shippingDiscountAmount;
                if ($cartRules[$rule->getId()] < 0.0) {
                    $baseDiscountAmount += $cartRules[$rule->getId()];
                    $discountAmount += $cartRules[$rule->getId()];
                }
            }
            if ($availableDiscountAmount <= 0) {
                $this->deltaPriceRound->reset($discountType);
            }

            //Customize here to fix the diffirent discount for last item (do not equal with discount amount)
            if ($cartRules[$rule->getId()] < 0.0 || ($cartRules[$rule->getId()] <= 0.1 && $cartRules[$rule->getId()] > 0.0)) {
                $baseDiscountAmount += $cartRules[$rule->getId()];
                $discountAmount += $cartRules[$rule->getId()];
                $cartRules[$rule->getId()] = 0;
            }

            if ($rule->getData("enable_exclude_skus")) {
                $excludeSkus = $this->helper->getExcludeSkusOfRule($rule);
                $productSku = $this->helper->getProductSkuOfItem($item);
                if (in_array($productSku, $excludeSkus)) {
                    $baseDiscountAmount = $discountAmount = 0.0;
                }

            }

            $discountData->setAmount($this->priceCurrency->roundPrice(min($itemPrice * $qty, $discountAmount)));
            $discountData->setBaseAmount($baseDiscountAmount);
            $discountData->setOriginalAmount(min($itemOriginalPrice * $qty, $discountAmount));
            $discountData->setBaseOriginalAmount($this->priceCurrency->roundPrice($baseItemOriginalPrice));
        }
        $quote->setCartFixedRules($cartRules);

        return $discountData;
    }
}
