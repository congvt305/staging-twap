<?php
declare(strict_types=1);

namespace CJ\Rgrid\Model;

use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;

class DuplicateRuleProcessor extends \Amasty\Rgrid\Model\DuplicateRuleProcessor
{
    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @param RuleRepositoryInterface $ruleRepository
     */
    public function __construct(RuleRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
        parent::__construct($ruleRepository);
    }

    /**
     * Customize fix error cannot duplicate in amasty extension
     *
     * @param int $ruleId
     * @return RuleInterface
     */
    public function execute(int $ruleId): RuleInterface
    {
        /** @var RuleInterface $rule */
        $rule = $this->ruleRepository->getById($ruleId);
        $rule->setRuleId(null);
        $attributes = $rule->getExtensionAttributes() ? : [];
        if (is_array($attributes)) {
            $attributes[self::COUNT_USAGE_COLUMN] = 0;
        } else {
            $attributes->setData(self::COUNT_USAGE_COLUMN, 0);
        }

        $rule = $this->ruleRepository->save($rule);

        return $rule;
    }
}
