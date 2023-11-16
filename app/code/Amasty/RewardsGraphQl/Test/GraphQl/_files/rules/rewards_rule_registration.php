<?php

declare(strict_types=1);

use Amasty\Rewards\Api\Data\RuleInterface;
use Amasty\Rewards\Api\Data\RuleInterfaceFactory;
use Amasty\Rewards\Api\RuleRepositoryInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var $repository RuleRepositoryInterface */
$repository = $objectManager->create(RuleRepositoryInterface::class);
$ruleFactory = $objectManager->create(RuleInterfaceFactory::class);

/** @var DataPersistorInterface $persistor */
$persistor = Bootstrap::getObjectManager()->get(DataPersistorInterface::class);

/** @var RuleInterface $rule */
$rule = $ruleFactory->create();

$rule->setData(
    [
        'customer_group_ids' => [0],
        'website_ids' => [
            $objectManager->get(StoreManagerInterface::class)->getWebsite()->getId(),
        ],
        'is_active' => 1,
        'name' => "Registration bonus",
        'action' => 'registration',
        'amount' => 15,
        'spent_amount' => 0,
        'recurring' => 0,
        'expiration_behavior' => 1,
    ]
);

$repository->save($rule);
$persistor->set('rewards_rule_registration_id', $rule->getRuleId());
