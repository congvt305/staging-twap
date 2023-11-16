<?php

declare(strict_types=1);

use Amasty\Rewards\Api\Data\ExpirationArgumentsInterface;
use Amasty\Rewards\Api\RewardsProviderInterface;
use Amasty\Rewards\Api\RewardsRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var DataPersistorInterface $persistor */
$persistor = Bootstrap::getObjectManager()->get(DataPersistorInterface::class);

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = Bootstrap::getObjectManager()->create(CustomerRepositoryInterface::class);

/** @var $rewardsRepository RewardsRepositoryInterface */
$rewardsRepository = $objectManager->create(RewardsRepositoryInterface::class);

/** @var RewardsProviderInterface $providerRewards */
$providerRewards = $objectManager->create(RewardsProviderInterface::class);

/** @var ExpirationArgumentsInterface $expire */
$expire = $objectManager->create(ExpirationArgumentsInterface::class);

$customer = $customerRepository->get('rewardspoints@amasty.com');
$customerId = $customer->getId();

$providerRewards->addPoints(10, $customerId, 'admin', 'comment', $expire);
$history = $rewardsRepository->getByCustomerId($customerId);
$historyId = 0;
foreach ($history as $k => $v) {
    if ($historyId < $v['id']) {
        $historyId = $v['id'];
    }
}

$persistor->set('rewards_added_admin_id', $historyId);
