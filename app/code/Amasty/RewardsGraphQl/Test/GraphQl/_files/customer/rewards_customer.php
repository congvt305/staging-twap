<?php

declare(strict_types=1);

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var $repository CustomerRepositoryInterface */
$repository = $objectManager->create(CustomerRepositoryInterface::class);

/** @var CustomerRegistry $customerRegistry */
$customerRegistry = $objectManager->get(CustomerRegistry::class);

/** @var DataPersistorInterface $persistor */
$persistor = Bootstrap::getObjectManager()->get(DataPersistorInterface::class);

/** @var Address $address  */
$address = Bootstrap::getObjectManager()->create(Address::class);

/** @var Customer $customer */
$customer = $objectManager->create(Customer::class);

$addressData = [
    'firstname' => 'John',
    'lastname' => 'Smith',
    'street' => ['Orange str, 67'],
    'city' => 'New York',
    'region_id' => 1,
    'country_id' => 'US',
    'postcode' => '37025',
    'telephone' => '+7000000001',
    'attribute_set_id' => 2,
    'company' => 'Am Name',
    'parent_id' => 1,
];
$address->isObjectNew(true);
$address->setData($addressData);
$address->save();
$addressId = $address->getId();
$customer->addAddress($address);

$customer->setWebsiteId(1)
    ->setEmail('rewardspoints@amasty.com')
    ->setPassword('rewardspassword')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setIsActive(1)
    ->setPrefix('Mr.')
    ->setFirstname('John')
    ->setMiddlename('A')
    ->setLastname('Smith')
    ->setSuffix('Esq.')
    ->setDefaultBilling($addressId)
    ->setDefaultShipping($addressId)
    ->setTaxvat('12')
    ->setGender(0);

$customer->isObjectNew(true);
$customer->save();

$persistor->set('rewards_customer_address_id', $addressId);
