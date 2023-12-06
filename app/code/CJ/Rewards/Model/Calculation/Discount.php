<?php
declare(strict_types=1);

namespace CJ\Rewards\Model\Calculation;

use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Amasty\Rewards\Model\Calculation\Discount\Applier;
use Amasty\Rewards\Model\Calculation\Distributor;
use Amasty\Rewards\Model\Config;
use Amasty\Rewards\Model\Points\Converter\ToMoney;
use Amasty\Rewards\Model\Quote\SpendingChecker;
use CJ\Rewards\Model\Data;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Class Discount
 * Override \Amasty\Rewards\Model\Calculation\Discount
 */
class Discount extends \Amasty\Rewards\Model\Calculation\Discount
{
    /**
     * @var Config
     */
    private Config $rewardsConfig;

    /**
     * @var Applier
     */
    private Applier $discountApplier;

    /**
     * @var Distributor
     */
    private Distributor $discountDistributor;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var ToMoney
     */
    private ToMoney $toMoney;

    /**
     * @var SpendingChecker
     */
    private SpendingChecker $spendingChecker;

    /**
     * @var TaxConfig
     */
    private TaxConfig $taxConfig;

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $promoItemHelper;

    /**
     * @var Data
     */
    private $rewardData;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var \CJ\Middleware\Helper\Data
     */
    private $middlewareHelper;

    /**
     * @param Config $rewardsConfig
     * @param Applier $discountApplier
     * @param Distributor $discountDistributor
     * @param StoreManagerInterface $storeManager
     * @param SpendingChecker $spendingChecker
     * @param ToMoney $toMoney
     * @param TaxConfig $taxConfig
     * @param \Amasty\Promo\Helper\Item $promoItemHelper
     * @param Data $rewardData
     * @param MessageManagerInterface $messageManager
     * @param \CJ\Middleware\Helper\Data $middlewareHelper
     */
    public function __construct(
        Config $rewardsConfig,
        Applier $discountApplier,
        Distributor $discountDistributor,
        StoreManagerInterface $storeManager,
        SpendingChecker $spendingChecker,
        ToMoney $toMoney,
        TaxConfig $taxConfig,
        \Amasty\Promo\Helper\Item $promoItemHelper,
        Data $rewardData,
        MessageManagerInterface $messageManager,
        \CJ\Middleware\Helper\Data $middlewareHelper
    ) {
        $this->rewardsConfig = $rewardsConfig;
        $this->discountApplier = $discountApplier;
        $this->discountDistributor = $discountDistributor;
        $this->storeManager = $storeManager;
        $this->spendingChecker = $spendingChecker;
        $this->toMoney = $toMoney;
        $this->taxConfig = $taxConfig;
        $this->promoItemHelper = $promoItemHelper;
        $this->rewardData = $rewardData;
        $this->messageManager = $messageManager;
        $this->middlewareHelper = $middlewareHelper;
        parent::__construct(
            $rewardsConfig,
            $discountApplier,
            $discountDistributor,
            $storeManager,
            $spendingChecker,
            $toMoney,
            $taxConfig
        );
    }

    /**
     * Calculate discount
     *
     * @param Item[] $quoteItems
     * @param Total $total
     * @param float $points
     *
     * @return float
     */
    public function calculateDiscount(array $quoteItems, Total $total, float $points): float
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $rate = $this->rewardsConfig->getPointsRate($storeId);
        $items = $this->filterItems($quoteItems, $storeId);
        $allCartPrice = $this->getAllItemsPrice($items);
        $isEnableShowListOptionRewardPoint = $this->rewardData->isEnableShowListOptionRewardPoint();
        if ($isEnableShowListOptionRewardPoint) {
            $listOptions = $this->rewardData->getListOptionRewardPoint();
            $amountDiscount = $listOptions[$points] ?? 0;
            if ($allCartPrice < $amountDiscount) {
                $this->messageManager->addErrorMessage(__('Can not use rewards point because reward discount amount is  greater than grand total'));
                return 0;
            }
        }
        $isDecimalFormat = $this->middlewareHelper->getIsDecimalFormat('store', $storeId);
        if (!$points || !$rate || !$items || !$allCartPrice) {
            return 0;
        }

        usort($items, [$this, 'sortItems']);

        $roundRule = $this->rewardsConfig->getRoundRule($storeId);
        $basePoints = $this->toMoney->convert($points, $storeId, $allCartPrice);
        $percent = ($basePoints * 100) / $allCartPrice;
        $itemsDiscount = $this->discountDistributor->distribute($items, $basePoints, $percent);
        if ($isEnableShowListOptionRewardPoint) {
            $rate = $points / $basePoints;
        }

        $discountValue = 0;
        $oddTotal = 0;
        foreach ($items as $item) {
            $itemDiscount = $itemsDiscount[$item->getId()] ?? 0;
            if ($itemDiscount > 0) {
                $oddDiscountAmount = $itemDiscount - ((int)$itemDiscount);
                if ($oddDiscountAmount > 0) {
                    $oddTotal += $oddDiscountAmount;
                }
            }
            if (!$isDecimalFormat) {
                $this->discountApplier->apply($item, $total, (int)$itemDiscount, $rate);
            } else {
                $this->discountApplier->apply($item, $total, $itemDiscount, $rate);
            }

            $discountValue += $itemsDiscount[$item->getId()];
        }
        if (!$isDecimalFormat) {
            $this->addOddTotal($items, $total, $oddTotal, $rate);
        }

        $appliedPoints = $discountValue * $rate;


        if ($roundRule === 'up') {
            $appliedPoints = ceil($appliedPoints);
        }

        return (float)$appliedPoints;
    }

    /**
     * Filter items
     *
     * @param array $items
     * @param int $storeId
     * @return array
     */
    private function filterItems(array $items, int $storeId): array
    {
        $filteredItems = [];
        foreach ($items as $item) {
            if (!$this->spendingChecker->isPossibleSpendOnItem($item, $storeId) || $item->getBaseRowTotal() <= 0) {
                $item->setData(EntityInterface::POINTS_SPENT, 0);
                continue;
            }
            $filteredItems[$item->getId()] = $item;
        }
        return $filteredItems;
    }

    /**
     * Get all item price
     *
     * @param Item[] $items
     * @return float
     */
    private function getAllItemsPrice(array $items): float
    {
        $allItemsPrice = 0;

        foreach ($items as $item) {
            $allItemsPrice += $this->getRealItemPrice($item);
        }

        return (float)$allItemsPrice;
    }

    /**
     * Get real item price
     *
     * @param AbstractItem $item
     * @return float
     */
    private function getRealItemPrice(AbstractItem $item): float
    {
        if (!$this->taxConfig->discountTax()) {
            $itemPrice = $item->getBasePrice();
        } else {
            $itemPrice = $item->getBasePriceInclTax();
        }
        $qty = $item->getQty();
        if ($item->getParentItem()) {
            $qty = $item->getQty() * $item->getParentItem()->getQty();
        }
        $realPrice = ($itemPrice * $qty) - $item->getBaseDiscountAmount();

        return (float)max(0, $realPrice);
    }

    /**
     * Sorting items before apply reward points
     * cheapest should go first
     *
     * @param AbstractItem $itemA
     * @param AbstractItem $itemB
     *
     * @return int
     */
    private function sortItems(AbstractItem $itemA, AbstractItem $itemB): int
    {
        if ($this->getRealItemPrice($itemA) > $this->getRealItemPrice($itemB)) {
            return 1;
        }

        if ($this->getRealItemPrice($itemA) < $this->getRealItemPrice($itemB)) {
            return -1;
        }

        return 0;
    }

    /**
     * Add odd total to discount to round discount
     *
     * @param array $items
     * @param Total $total
     * @param float $oddTotal
     * @param float $rate
     * @return void
     */
    private function addOddTotal($items, $total, $oddTotal, $rate)
    {
        $oddTotal = round($oddTotal); // Should round or int
        $isAddOddTotal = false;
        if ($oddTotal > 0 && $total->getSubtotal() + $total->getDiscountAmount() >= $oddTotal) {
            foreach ($items as $item) {
                // to determine the child item discount, we calculate the parent
                if ($item->getDiscountAmount() > 0 && !$this->promoItemHelper->isPromoItem($item)) {
                    $isAddOddTotal = true;
                    $item->setData(EntityInterface::POINTS_SPENT, $item->getData(EntityInterface::POINTS_SPENT) + $oddTotal * $rate);
                    $item->setDiscountAmount($item->getDiscountAmount() + $oddTotal);
                    $item->setBaseDiscountAmount($item->getBaseDiscountAmount() + $oddTotal);
                    $total->addTotalAmount('discount', -$oddTotal);
                    $total->addBaseTotalAmount('discount', -$oddTotal);
                    break;
                }
            }

            //Add in case the discount amount for each item < 1 so get item which is not gift to add discount
            if (!$isAddOddTotal) {
                foreach ($items as $item) {
                    if ($item->getPrice() && !$this->promoItemHelper->isPromoItem($item)) {
                        $item->setData(EntityInterface::POINTS_SPENT, $item->getData(EntityInterface::POINTS_SPENT) + $oddTotal * $rate);
                        $item->setDiscountAmount($item->getDiscountAmount() + $oddTotal);
                        $item->setBaseDiscountAmount($item->getBaseDiscountAmount() + $oddTotal);
                        $total->addTotalAmount('discount', -$oddTotal);
                        $total->addBaseTotalAmount('discount', -$oddTotal);
                        break;
                    }
                }
            }
        }
    }
}
