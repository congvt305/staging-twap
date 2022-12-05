<?php

namespace CJ\CustomCustomer\Console;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RemoveUnusedCustomerGroup
 */
class RemoveUnusedCustomerGroup extends \Symfony\Component\Console\Command\Command
{
    const NAME = 'customer:unused_customer_group:remove';

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var array
     */
    protected $unusedCustomerGroups = [
        'Wholesale' => [
            GroupInterface::ID => 2,
            GroupInterface::CODE => 'Wholesale'
        ],
        'Retailer' => [
            GroupInterface::ID => 3,
            GroupInterface::CODE => 'Retailer'
        ]
    ];

    /**
     * @param GroupRepositoryInterface $groupRepository
     * @param string|null $name
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        string $name = null
    ) {
        $this->groupRepository = $groupRepository;
        parent::__construct($name);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Command to remove unused customer groups');

        parent::configure();
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Begin removing the unused customer groups");
        foreach ($this->unusedCustomerGroups as $key => $value) {
            $id = $value[GroupInterface::ID];
            try {
                $this->groupRepository->deleteById($id);
                $output->writeln(__("You deleted the customer group \"%1\"", $key));
            } catch (NoSuchEntityException $e) {
                $output->writeln(__("The customer group no longer exists. \"%1\"", $key));
            } catch (\Exception $e) {
                $output->writeln(__("Cannot deleted the customer group \"%1\". Exception: %2", $key, $e->getMessage()));
            }
        }
        $output->writeln("Finished.");
    }
}
