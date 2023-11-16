<?php

declare(strict_types=1);

use Amasty\Rewards\Api\RewardsRepositoryInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var DataPersistorInterface $persistor */
$persistor = Bootstrap::getObjectManager()->get(DataPersistorInterface::class);

/** @var $repository RewardsRepositoryInterface */
$repository = $objectManager->create(RewardsRepositoryInterface::class);

$addedId = $persistor->get('rewards_added_admin_id');
if ($addedId) {
    $repository->deleteById((int)$addedId);
}
$persistor->clear('rewards_added_admin_id');
