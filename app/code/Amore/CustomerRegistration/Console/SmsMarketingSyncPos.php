<?php

namespace Amore\CustomerRegistration\Console;

use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class SmsMarketingSyncPos extends \Symfony\Component\Console\Command\Command
{
    const NAME = 'pos:smsmarketing:sync';
    const TIME_TO_MIGRATE = '2022-03-24';
    const STORE_CODE_APPLY = ['default', 'tw_laneige'];
    const QUANTITY = 'qty';
    const QUANTITY_DEFAULT = 100;

    /**
     * @var CustomerCollectionFactory
     */
    protected CustomerCollectionFactory $customerCollectionFactory;

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
     * @param string|null $name
     */
    public function __construct(
        CustomerCollectionFactory $customerCollectionFactory,
        StoreManagerInterface $storeManager,
        AddressRegistry $addressRegistry,
        string $name = null
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->storeManager = $storeManager;
        $this->addressRegistry = $addressRegistry;
        parent::__construct($name);
    }
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('POS - EC SMS Marketing synchronization');
        $this->addOption(
            self::QUANTITY,
            null,
            InputOption::VALUE_OPTIONAL, 'Qty to run'
        );
        parent::configure();
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $quantity = $input->getOption(self::QUANTITY) ? $input->getOption(self::QUANTITY) : self::QUANTITY_DEFAULT;

        $customers = $this->customerCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('created_at', ['lteq' => self::TIME_TO_MIGRATE])
            ->addFieldToFilter('updated_at', ['lteq' => self::TIME_TO_MIGRATE])
            ->addFieldToFilter('store_id', ['in' => $this->getWebsiteApply()])
            ->addAttributeToFilter('call_subscription_status', 1);
        $customers
            ->setPageSize($quantity)
            ->setCurPage(1)
            ->load();
        $output->writeln("Customers' SMS marketing synchronization has started");
        $output->writeln("Check more detail of progress at this file var/log/pos.log");
        foreach ($customers as $customer) {
            try {
                $this->disableAddressValidation($customer);
                $customer->setSmsSubscriptionStatus(1);
                $customer->save();
                $output->writeln("Done sync customer with ID: " . $customer->getEntityId());
            } catch (\Exception $exception) {
                $output->writeln('Exception when sync customer with ID: ' . $customer->getEntityId());
                $output->writeln('Exception message: ' . $exception->getMessage());
            } catch (\Throwable $throwable) {
                $output->writeln('Throwable when sync customer with ID: ' . $customer->getEntityId());
                $output->writeln('Throwable message: ' . $throwable->getMessage());
            }
        }
        $output->writeln("All Process Done");
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
    private function getWebsiteApply()
    {
        $storeApply = [];
        foreach ($this->storeManager->getStores() as $store) {
            if (in_array($store->getCode(), self::STORE_CODE_APPLY)) {
                $storeApply[] = $store->getId();
            }
        }
        return $storeApply;
    }
}
