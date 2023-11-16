<?php

declare(strict_types=1);

use Amasty\Rewards\Api\RewardsProviderInterface;
use Amasty\Rewards\Api\RewardsRepositoryInterface;
use Amasty\Rewards\Api\RuleRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = Bootstrap::getObjectManager()->create(CustomerRepositoryInterface::class);

/** @var DataPersistorInterface $persistor */
$persistor = Bootstrap::getObjectManager()->get(DataPersistorInterface::class);

/** @var RewardsProviderInterface $providerRewards */
$providerRewards = $objectManager->create(RewardsProviderInterface::class);

/** @var $rewardsRepository RewardsRepositoryInterface */
$rewardsRepository = $objectManager->create(RewardsRepositoryInterface::class);

/** @var RuleRepositoryInterface $ruleRepository */
$ruleRepository = $objectManager->create(RuleRepositoryInterface::class);

$customer = $customerRepository->get('rewardspoints@amasty.com');
$customerId = $customer->getId();

$ruleId = $persistor->get('rewards_rule_registration_id');

if ($ruleId) {
    $rule = $ruleRepository->get((int)$ruleId);
    $providerRewards->addPointsByRule($rule, $customerId, $customer->getStoreId());
    $history = $rewardsRepository->getByCustomerId($customerId);
    $historyId = 0;
    foreach ($history as $k => $v) {
        if ($historyId < $v['id']) {
            $historyId = $v['id'];
        }
    }
    $persistor->set('rewards_added_registration_id', $historyId);
}
