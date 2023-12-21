<?php

declare(strict_types=1);

use Amasty\Rewards\Api\Data\RuleInterface;
use Amasty\Rewards\Api\Data\RuleInterfaceFactory;
use Amasty\Rewards\Api\RuleRepositoryInterface;
use Magento\Framework\App\Request\DataPersistor;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var $repository RuleRepositoryInterface */
$repository = $objectManager->create(RuleRepositoryInterface::class);
$ruleFactory = $objectManager->create(RuleInterfaceFactory::class);

/** @var DataPersistor $persistor */
$persistor = Bootstrap::getObjectManager()->get(DataPersistorInterface::class);

/** @var RuleInterface $rule */
$rule = $ruleFactory->create();

$rule->setData(
    [
        'customer_group_ids' => [0,1],
        'website_ids' => [
            $objectManager->get(StoreManagerInterface::class)->getWebsite()->getId(),
        ],
        'is_active' => 1,
        'name' => "Newsletter subscription bonus",
        'action' => 'subscription',
        'amount' => 5,
        'spent_amount' => 0,
        'recurring' => 0,
        'expiration_behavior' => 2,
        'expiration_period' => 10,
    ]
);

$repository->save($rule);
$persistor->set('rewards_rule_newsletter_id', $rule->getRuleId());
