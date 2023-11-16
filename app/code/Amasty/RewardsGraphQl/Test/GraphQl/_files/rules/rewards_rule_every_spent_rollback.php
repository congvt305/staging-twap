<?php

declare(strict_types=1);

use Amasty\Rewards\Api\Data\RuleInterface;
use Amasty\Rewards\Api\RuleRepositoryInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var DataPersistorInterface $persistor */
$persistor = Bootstrap::getObjectManager()->get(DataPersistorInterface::class);

/** @var $repository RuleRepositoryInterface */
$repository = $objectManager->create(RuleRepositoryInterface::class);

/** @var RuleInterface $rule */
$ruleId = $persistor->get('rewards_rule_every_spent_id');
if ($ruleId) {
    $repository->deleteById((int)$ruleId);
}
$persistor->clear('rewards_rule_every_spent_id');
