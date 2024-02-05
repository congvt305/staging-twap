<?php
declare(strict_types=1);

namespace Sapt\OneStepCheckoutCore\Plugin\Quote;

use Amasty\Coupons\Api\Data\DiscountBreakdownLineInterface;
use Amasty\Coupons\Api\Data\DiscountBreakdownLineInterfaceFactory;
use Amasty\Coupons\Model\DiscountCollector as AmastyDiscountCollector;
use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Amasty\Rewards\Model\Config;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\TotalsExtensionFactory;
use Magento\SalesRule\Model\Quote\Discount as DiscountCollector;

/**
 * Class CartTotalRepositoryInterfacePlugin
 */
class CartTotalRepositoryInterfacePlugin
{
    /**
     * Reserved rule ID for points discount
     */
    const RESERVED_POINT_RULE_ID = 0;

    /**
     * List of store codes which apply these changes
     */
    const APPLIED_STORES = [
        'tw_laneige'
    ];

    /**
     * @var AmastyDiscountCollector
     */
    protected AmastyDiscountCollector $discountCollector;

    /**
     * @var CartRepositoryInterface
     */
    protected CartRepositoryInterface $quoteRepository;

    /**
     * @var TotalsExtensionFactory
     */
    protected TotalsExtensionFactory $totalsExtensionFactory;

    /**
     * @var DiscountBreakdownLineInterfaceFactory
     */
    protected DiscountBreakdownLineInterfaceFactory $discountBreakdownFactory;

    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @param AmastyDiscountCollector $discountCollector
     * @param CartRepositoryInterface $quoteRepository
     * @param TotalsExtensionFactory $totalsExtensionFactory
     * @param DiscountBreakdownLineInterfaceFactory $discountBreakdownFactory
     * @param Config $config
     */
    public function __construct(
        AmastyDiscountCollector $discountCollector,
        CartRepositoryInterface $quoteRepository,
        TotalsExtensionFactory $totalsExtensionFactory,
        DiscountBreakdownLineInterfaceFactory $discountBreakdownFactory,
        Config $config
    ) {
        $this->discountCollector = $discountCollector;
        $this->quoteRepository = $quoteRepository;
        $this->totalsExtensionFactory = $totalsExtensionFactory;
        $this->discountBreakdownFactory = $discountBreakdownFactory;
        $this->config = $config;
    }

    /**
     * Remove cart rule labels from Discount title and point breakdown line
     *
     * @param CartTotalRepositoryInterface $subject
     * @param $result
     * @param $cartId
     * @return mixed
     */
    public function afterGet(
        CartTotalRepositoryInterface $subject,
        $result,
        $cartId
    ) {
        try {
            $quote = $this->quoteRepository->getActive($cartId);
            if (in_array($quote->getStore()->getCode(), self::APPLIED_STORES)) {
                $this->resetDiscountTitle($result);
                $this->addPointBreakdownLine($quote, $result);
            }
        } catch (\Exception $e) {
            return $result;
        }

        return $result;
    }

    /**
     * Remove all rule's titles from Discount title
     *
     * @param $quoteTotal
     * @return mixed
     */
    protected function resetDiscountTitle($quoteTotal)
    {
        $totalSegments = $quoteTotal->getTotalSegments() ?? [];
        foreach ($totalSegments as $index => $segment) {
            if ($segment->getCode() == DiscountCollector::COLLECTOR_TYPE_CODE) {
                $segment->setTitle(__('Discount')->render());
                $totalSegments[$index] = $segment;

                $quoteTotal->setTotalSegments($totalSegments);
                break;
            }
        }

        return $quoteTotal;
    }

    /**
     * Add point breakdown line to order summary
     *
     * @param $quote
     * @param $quoteTotal
     * @return mixed
     */
    protected function addPointBreakdownLine($quote, $quoteTotal)
    {
        $appliedPoints = (float) $quote->getData(EntityInterface::POINTS_SPENT);
        if ($appliedPoints <= 0) {
            return $quoteTotal;
        }

        $rate = $this->config->getPointsRate($quote->getStoreId());
        $appliedAmount = $appliedPoints / $rate;
        $formattedPrice = $quote->getStore()->getCurrentCurrency()->format($appliedAmount, [], false);

        $extensionAttributes = $quoteTotal->getExtensionAttributes();
        if (!$extensionAttributes) {
            $extensionAttributes = $this->totalsExtensionFactory->create();
        }

        $discounts = [];
        if (!empty($extensionAttributes->getAmcouponDiscountBreakdown())) {
            $discounts = $extensionAttributes->getAmcouponDiscountBreakdown();
            foreach ($discounts as $index => $discount) {
                if ($discount && $discount->getRuleId() === self::RESERVED_POINT_RULE_ID) {
                    unset($discounts[$index]);
                    break;
                }
            }
        }

        $discounts[] = $this->discountBreakdownFactory->create([
            'data' => [
                DiscountBreakdownLineInterface::RULE_ID => self::RESERVED_POINT_RULE_ID,
                DiscountBreakdownLineInterface::RULE_NAME => __('Used %1 reward points', $appliedPoints)->render(),
                DiscountBreakdownLineInterface::RULE_AMOUNT => "-{$formattedPrice}"
            ]
        ]);
        $extensionAttributes->setAmcouponDiscountBreakdown($discounts);

        return $quoteTotal;
    }
}
