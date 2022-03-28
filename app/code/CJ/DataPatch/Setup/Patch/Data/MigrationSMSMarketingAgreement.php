<?php

namespace CJ\DataPatch\Setup\Patch\Data;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\AddressRegistry;

class MigrationSMSMarketingAgreement implements DataPatchInterface
{
    const TIME_TO_MIGRATE = '2022-03-24';
    const STORE_CODE_APPLY = ['default', 'tw_laneige'];

    /**
     * @var CustomerCollectionFactory
     */
    protected CustomerCollectionFactory $customerCollectionFactory;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var AddressRegistry|mixed
     */
    protected AddressRegistry $addressRegistry;

    /**
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param AddressRegistry $addressRegistry
     * @param LoggerInterface $logger
     */
    public function __construct(
        CustomerCollectionFactory $customerCollectionFactory,
        StoreManagerInterface $storeManager,
        AddressRegistry $addressRegistry,
        LoggerInterface $logger
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->storeManager = $storeManager;
        $this->addressRegistry = $addressRegistry;
        $this->logger = $logger;
    }

    /**
     * @return MigrationSMSMarketingAgreement|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply()
    {
        $customers = $this->customerCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('created_at', ['lteq' => self::TIME_TO_MIGRATE])
            ->addFieldToFilter('updated_at', ['lteq' => self::TIME_TO_MIGRATE])
            ->addFieldToFilter('store_id', ['in' => $this->getWebsiteApply()])
            ->addAttributeToFilter('call_subscription_status', 1);

        foreach ($customers as $customer) {
            try {
                $this->disableAddressValidation($customer);
                $customer->setSmsSubscriptionStatus(1);
                $customer->save();
            } catch (\Exception $exception) {
                $this->logger->error('Error when sync customer with ID', [
                    'customerID' => $customer->getEntityId(),
                    'errorMessage' => $exception->getMessage()
                ]);
            }
        }
    }

    /**
     * @param $customer
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function disableAddressValidation($customer)
    {
        foreach ($customer->getAddresses() as $address) {
            $addressModel = $this->addressRegistry->retrieve($address->getId());
            $addressModel->setShouldIgnoreValidation(true);
        }
    }

    /**
     * @return array
     */
    protected function getWebsiteApply()
    {
        $storeApply = [];
        foreach ($this->storeManager->getStores() as $store) {
            if (in_array($store->getCode(), self::STORE_CODE_APPLY)) {
                $storeApply[] = $store->getId();
            }
        }
        return $storeApply;
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
