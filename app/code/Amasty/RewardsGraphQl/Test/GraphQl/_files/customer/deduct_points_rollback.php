<?php

declare(strict_types=1);

use Amasty\Rewards\Api\HistoryRepositoryInterface;
use Amasty\Rewards\Api\RewardsRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var DataPersistorInterface $persistor */
$persistor = Bootstrap::getObjectManager()->get(DataPersistorInterface::class);

/** @var $repository RewardsRepositoryInterface */
$repository = $objectManager->create(RewardsRepositoryInterface::class);

/** @var $historyRepository HistoryRepositoryInterface */
$historyRepository = $objectManager->create(HistoryRepositoryInterface::class);

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = Bootstrap::getObjectManager()->create(CustomerRepositoryInterface::class);

$addedId = $persistor->get('rewards_deduct_points_id');
if ($addedId) {
    $repository->deleteById((int)$addedId);
}
$persistor->clear('rewards_deduct_points_id');
