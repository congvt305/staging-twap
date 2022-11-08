<?php
declare(strict_types=1);

namespace CJ\AmastyPromo\Override\Model\ItemRegistry;

use Amasty\Promo\Api\Data\GiftRuleInterface;
use Amasty\Promo\Model\ItemRegistry\PromoItemData;
use Amasty\Promo\Model\ResourceModel\Rule;
use Magento\SalesRule\Api\RuleRepositoryInterface;

class PromoItemRemover extends \Amasty\Promo\Model\ItemRegistry\PromoItemRemover
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @var Rule
     */
    private $rule;

    /**
     * @param Rule $rule
     * @param RuleRepositoryInterface $ruleRepository
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     */
    public function __construct(
        Rule $rule,
        RuleRepositoryInterface $ruleRepository,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
    ) {
        parent::__construct($rule, $ruleRepository);
       $this->ruleCollectionFactory = $ruleCollectionFactory;
       $this->rule = $rule;
    }

    /**
     * @param PromoItemData[] $items
     * @return PromoItemData[]
     */
    public function execute(array $items): array
    {
        $ruleIds = [];
        $allSkus = [];
        $availableSkus = [];
        $sameProductSkus = [];

        foreach ($items as $key => $item) {
            if (!in_array($item->getRuleId(), $ruleIds)) {
                $ruleIds[] = $item->getRuleId();
            }

            if (!in_array($item->getSku(), $allSkus)) {
                $allSkus[$key] = $item->getSku();
            }

            // Rule with action == SAME_PRODUCT doesn't have setting 'Promo Skus'.
            // So we don't need to remove items for such rule.
            $ruleCollection = $this->ruleCollectionFactory->create();
            $ruleCollection->addFieldToFilter('row_id', $item->getRuleId());
            $rule = $ruleCollection->getFirstItem();
            if ($rule->getSimpleAction() === GiftRuleInterface::SAME_PRODUCT) {
                $sameProductSkus[] = $item->getSku();
            }
        }

        $ruleSkus = $this->rule->isApplicable($ruleIds, 'sku');

        foreach ($ruleSkus as $skus) {
            $availableSkus[] = explode(',', $skus['sku']);
        }

        $availableSkus = array_merge([], ...$availableSkus);
        $availableSkus = array_map('trim', $availableSkus);

        foreach ($allSkus as $key => $sku) {
            if (!in_array($sku, $availableSkus) && !in_array($sku, $sameProductSkus)) {
                unset($items[$key]);
            }
        }

        return $items;
    }
}
