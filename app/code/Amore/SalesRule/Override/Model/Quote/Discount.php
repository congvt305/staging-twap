<?php

namespace Amore\SalesRule\Override\Model\Quote;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Api\Data\DiscountDataInterface;
use Magento\SalesRule\Api\Data\DiscountDataInterfaceFactory;
use Magento\SalesRule\Api\Data\RuleDiscountInterfaceFactory;
use Magento\SalesRule\Model\Data\RuleDiscount;
use Magento\SalesRule\Model\Discount\PostProcessorFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RulesApplier;
use Magento\SalesRule\Model\Validator;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Discount totals calculation model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Discount extends \Magento\SalesRule\Model\Quote\Discount
{
    const PAYNOW_VISA = 'paynow-visa';

    const PAYNOW_WALLET = 'paynow-wallet';

    const PAYNOW = 'paynow';


    /**
     * @var RuleDiscountInterfaceFactory
     */
    private $discountInterfaceFactory;

    /**
     * @var DiscountDataInterfaceFactory
     */
    private $discountDataInterfaceFactory;

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    protected $promoItemHelper;

    protected $stores = [
        self::TW_LNG,
        self::MY_LNG,
        self::VN_LNG,
        self::VN_SWS,
        self::TW_SWS
    ];

    const VN_LNG = 'vn_laneige';
    const VN_SWS = 'vn_sulwhasoo';
    const TW_LNG = 'tw_laneige';
    const TW_SWS = 'default';
    const MY_LNG = 'my_laneige';


    public function __construct(
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Validator $validator,
        PriceCurrencyInterface $priceCurrency,
        \Amasty\Promo\Helper\Item $promoItemHelper,
        RuleDiscountInterfaceFactory $discountInterfaceFactory = null,
        DiscountDataInterfaceFactory $discountDataInterfaceFactory = null,
        RulesApplier $rulesApplier = null
    ) {
        parent::__construct($eventManager, $storeManager, $validator, $priceCurrency, $discountInterfaceFactory, $discountDataInterfaceFactory, $rulesApplier);
        $this->storeManager = $storeManager;
        $this->discountInterfaceFactory = $discountInterfaceFactory
            ?: ObjectManager::getInstance()->get(RuleDiscountInterfaceFactory::class);
        $this->discountDataInterfaceFactory = $discountDataInterfaceFactory
            ?: ObjectManager::getInstance()->get(DiscountDataInterfaceFactory::class);
        $this->rulesApplier = $rulesApplier
            ?: ObjectManager::getInstance()->get(RulesApplier::class);
        $this->promoItemHelper = $promoItemHelper;
    }

    /**
     * Collect address discount amount
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        $storeCode = $this->storeManager->getStore()->getCode();
        if (in_array($storeCode, $this->stores)) {

            AbstractTotal::collect($quote, $shippingAssignment, $total);

            $store = $this->storeManager->getStore($quote->getStoreId());
            $address = $shippingAssignment->getShipping()->getAddress();

            if ($quote->currentPaymentWasSet()) {
                $address->setPaymentMethod($quote->getPayment()->getMethod());
            }
            //set temp to calculate paynow-visa and paynow-wallet as paynow payment
            if ($storeCode == self::VN_LNG) {
                if ($quote->getPayment()->getMethod() == self::PAYNOW_WALLET || $quote->getPayment()->getMethod() == self::PAYNOW_VISA) {
                    $address->setPaymentMethod(self::PAYNOW);
                }
            }

            $this->calculator->reset($address);
            //Customize here
            $address->setDiscountAmount(0);
            $address->setBaseDiscountAmount(0);
            $address->setBaseGrandTotal($address->getBaseSubtotal());
            $address->setGrandTotal($address->getSubtotal());
            //End of customize
            $itemsAggregate = [];
            foreach ($shippingAssignment->getItems() as $item) {
                $itemId = $item->getId();
                $itemsAggregate[$itemId] = $item;
            }
            $items = [];
            foreach ($quote->getAllAddresses() as $quoteAddress) {
                foreach ($quoteAddress->getAllItems() as $item) {
                    $items[] = $item;
                }
            }
            if (!$items || !$itemsAggregate) {
                return $this;
            }

            $eventArgs = [
                'website_id' => $store->getWebsiteId(),
                'customer_group_id' => $quote->getCustomerGroupId(),
                'coupon_code' => $quote->getCouponCode(),
            ];
            $address->setDiscountDescription([]);
            $address->getExtensionAttributes()->setDiscounts([]);
            $this->addressDiscountAggregator = [];
            $address->setCartFixedRules([]);
            $quote->setCartFixedRules([]);
            foreach ($items as $item) {
                if (!$this->promoItemHelper->isPromoItem($item)) {
                    $this->rulesApplier->setAppliedRuleIds($item, []);
                    if ($item->getExtensionAttributes()) {
                        $item->getExtensionAttributes()->setDiscounts(null);
                    }
                    $item->setDiscountAmount(0);
                    $item->setBaseDiscountAmount(0);
                    $item->setDiscountPercent(0);
                    if ($item->getChildren() && $item->isChildrenCalculated()) {
                        foreach ($item->getChildren() as $child) {
                            $child->setDiscountAmount(0);
                            $child->setBaseDiscountAmount(0);
                            $child->setDiscountPercent(0);
                        }
                    }
                }
            }
            $this->calculator->init($store->getWebsiteId(), $quote->getCustomerGroupId(), $quote->getCouponCode());
            $address->setBaseSubtotalWithDiscount($address->getQuote()->getBaseSubtotal() + $address->getQuote()->getDiscountAmount());
            $address->setSubtotalWithDiscount($address->getQuote()->getSubtotal() + $address->getQuote()->getDiscountAmount());

            $this->calculator->initTotals($items, $address);
            $items = $this->calculator->sortItemsByPriority($items, $address);
            $rules = $this->calculator->getRules($address);
            /** @var Rule $rule */
            foreach ($rules as $rule) {
                /** @var Item $item */
                foreach ($items as $item) {
                    if ($item->getNoDiscount() || !$this->calculator->canApplyDiscount($item) || $item->getParentItem()) {
                        continue;
                    }
                    $eventArgs['item'] = $item;
                    $this->eventManager->dispatch('sales_quote_address_discount_item', $eventArgs);
                    $this->calculator->process($item, $rule);
                }
                $appliedRuleIds = $quote->getAppliedRuleIds() ? explode(',', $quote->getAppliedRuleIds()) : [];
                if ($rule->getStopRulesProcessing() && in_array($rule->getId(), $appliedRuleIds)) {
                    break;
                }
                $this->calculator->initTotals($items, $address);
            }
            $oddTotal = 0;
            foreach ($items as $item) {
                if (!isset($itemsAggregate[$item->getId()])) {
                    continue;
                }
                if ($item->getParentItem()) {
                    continue;
                } elseif ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        $eventArgs['item'] = $child;
                        $this->eventManager->dispatch('sales_quote_address_discount_item', $eventArgs);
                        $this->aggregateItemDiscount($child, $total);
                        // Calculate odd Total
                        $oddTotal += $child->getOddDiscountAmount();
                        $child->setOddDiscountAmount(0);
                    }
                }
                $this->aggregateItemDiscount($item, $total);
                // Calculate odd Total
                $oddTotal += $item->getOddDiscountAmount();
                $item->setOddDiscountAmount(0);
                if ($item->getExtensionAttributes()) {
                    $this->aggregateDiscountPerRule($item, $address);
                }
            }
            // Custom for add odd number to first Item, avoid add discount over subtotal
            $oddTotal = round($oddTotal); // Should round or int
            if ($oddTotal > 0 && $total->getSubtotal() + $total->getDiscountAmount() >= $oddTotal) {
                foreach ($items as $item) {
                    // to determine the child item discount, we calculate the parent
                    if ($item->getDiscountAmount() > 0) {
                        $item->setDiscountAmount($item->getDiscountAmount() + $oddTotal);
                        $item->setBaseDiscountAmount($item->getBaseDiscountAmount() + $oddTotal);
                        $total->addTotalAmount($this->getCode(), -$oddTotal);
                        $total->addBaseTotalAmount($this->getCode(), -$oddTotal);
                        break;
                    }
                }
            }
            // end custom

            $this->calculator->prepareDescription($address);
            $total->setDiscountDescription($address->getDiscountDescription());
            $total->setSubtotalWithDiscount($total->getSubtotal() + $total->getDiscountAmount());
            $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() + $total->getBaseDiscountAmount());
            $address->setDiscountAmount($total->getDiscountAmount());
            $address->setBaseDiscountAmount($total->getBaseDiscountAmount());

            //set back current payment method after calculate
            if ($storeCode == self::VN_LNG) {
                if ($quote->getPayment()->getMethod() == self::PAYNOW_WALLET || $quote->getPayment()->getMethod() == self::PAYNOW_VISA) {
                    $address->setPaymentMethod($quote->getPayment()->getMethod());
                }
            }
            return $this;
        } else {
            parent::collect($quote, $shippingAssignment, $total);
        }
    }

    /**
     * Aggregates discount per rule
     *
     * @param AbstractItem $item
     * @param AddressInterface $address
     * @return void
     */
    private function aggregateDiscountPerRule(
        AbstractItem $item,
        AddressInterface $address
    ) {
        $discountBreakdown = $item->getExtensionAttributes()->getDiscounts();
        if ($discountBreakdown) {
            foreach ($discountBreakdown as $value) {
                /* @var DiscountDataInterface $discount */
                $discount = $value->getDiscountData();
                $ruleLabel = $value->getRuleLabel();
                $ruleID = $value->getRuleID();
                if (isset($this->addressDiscountAggregator[$ruleID])) {
                    /** @var RuleDiscount $cartDiscount */
                    $cartDiscount = $this->addressDiscountAggregator[$ruleID];
                    $discountData = $cartDiscount->getDiscountData();
                    $discountData->setBaseAmount($discountData->getBaseAmount() + $discount->getBaseAmount());
                    $discountData->setAmount($discountData->getAmount() + $discount->getAmount());
                    $discountData->setOriginalAmount(
                        $discountData->getOriginalAmount() + $discount->getOriginalAmount()
                    );
                    $discountData->setBaseOriginalAmount(
                        $discountData->getBaseOriginalAmount() + $discount->getBaseOriginalAmount()
                    );
                } else {
                    $data = [
                        'amount' => $discount->getAmount(),
                        'base_amount' => $discount->getBaseAmount(),
                        'original_amount' => $discount->getOriginalAmount(),
                        'base_original_amount' => $discount->getBaseOriginalAmount()
                    ];
                    $discountData = $this->discountDataInterfaceFactory->create(['data' => $data]);
                    $data = [
                        'discount' => $discountData,
                        'rule' => $ruleLabel,
                        'rule_id' => $ruleID,
                    ];
                    /** @var RuleDiscount $cartDiscount */
                    $cartDiscount = $this->discountInterfaceFactory->create(['data' => $data]);
                    $this->addressDiscountAggregator[$ruleID] = $cartDiscount;
                }
            }
        }
        $address->getExtensionAttributes()->setDiscounts(array_values($this->addressDiscountAggregator));
    }
}
