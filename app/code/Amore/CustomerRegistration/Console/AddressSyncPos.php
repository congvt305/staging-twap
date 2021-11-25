<?php

namespace Amore\CustomerRegistration\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class AddressSyncPos
 */
class AddressSyncPos extends \Symfony\Component\Console\Command\Command
{
    const NAME = 'pos:address:sync';

    const KEY_WEBSITE_ID = 'website_id';

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaInterface
     */
    protected $searchCriteria;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroup
     */
    protected $filterGroup;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Set Default value = 9. Website ID = 9 is Vietnamese Website
     *
     * @var int
     */
    protected $_websiteId = 9;

    /**
     * AddressSyncPos Constructor
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        string $name = null
    ) {
        $this->customerRepository = $customerRepository;
        $this->searchCriteria = $searchCriteria;
        $this->filterGroup = $filterGroup;
        $this->filterBuilder = $filterBuilder;

        parent::__construct($name);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('POS - EC Customers address synchronization');
        $this->addOption(
            self::KEY_WEBSITE_ID,
            null,
            InputOption::VALUE_OPTIONAL, 'Website Id'
        );
        parent::configure();
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get all customers
        $websiteId = $input->getOption(self::KEY_WEBSITE_ID);
        if ($websiteId) {
            $this->_websiteId = $websiteId;
        }
        $this->filterGroup->setFilters([
            $this->filterBuilder->setField('website_id')
                ->setValue($this->_websiteId)
                ->create(),
        ]);
        $this->searchCriteria->setFilterGroups([$this->filterGroup]);
        $customerList = $this->customerRepository->getList($this->searchCriteria);
        $customerCount = $customerList->getTotalCount();
        if ($customerCount > 0) {
            $customers = $customerList->getItems();
            $output->writeln(sprintf("Found %d customers", $customerCount));
            $output->writeln("Customers' address synchronization has started");
            $output->writeln("Check more detail of progress at this file var/log/pos.log");
            foreach ($customers as $customerData) {
                try {
                    $this->customerRepository->save($customerData);
                } catch (\Exception $e) {
                    $output->writeln($e->getMessage());
                } catch (\Throwable $e) {
                    $output->writeln($e->getMessage());
                }
            }
            $output->writeln("DONE!");
            $output->writeln(sprintf("Sync total %d records", $customerCount));
        } else {
            $output->writeln("Customer not found.");
        }
    }
}
