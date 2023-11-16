<?php

declare(strict_types=1);

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Integration\Model\Oauth\Token\RequestThrottler;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = Bootstrap::getObjectManager()->create(CustomerRepositoryInterface::class);

/** @var DataPersistorInterface $persistor */
$persistor = Bootstrap::getObjectManager()->get(DataPersistorInterface::class);

/** @var AddressRepositoryInterface $addressRepository  */
$addressRepository = Bootstrap::getObjectManager()->get(AddressRepositoryInterface::class);

$addressId = $persistor->get('rewards_customer_address_id');
$addressRepository->deleteById($addressId);

$customer = $customerRepository->get('rewardspoints@amasty.com');
$customerRepository->delete($customer);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

/** @var RequestThrottler $throttler */
$throttler = Bootstrap::getObjectManager()->create(RequestThrottler::class);
$throttler->resetAuthenticationFailuresCount('rewardspoints@amasty.com', RequestThrottler::USER_TYPE_CUSTOMER);
