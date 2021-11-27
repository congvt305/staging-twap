<?php

namespace CJ\ChangeOrderStatus\Console;

use Magento\Framework\Exception\NotFoundException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ChangeOrderStatus
 */
class ChangeOrderStatus extends \Symfony\Component\Console\Command\Command
{
    const NAME = 'cj:order:change-status';

    const STORE_ID = 'store_id';

    /**
     * @var int
     */
    protected $storeId = 9;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param null $name
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        $name = null
    ) {
        $this->state = $state;
        $this->orderRepository = $orderRepository;
        $this->criteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($name);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->addOption(self::STORE_ID, null, InputOption::VALUE_OPTIONAL, 'Store Id')
            ->setDescription('Change the order\'s status to complete.');

        parent::configure();
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

            $storeId = $input->getOption(self::STORE_ID);
            if ($storeId) {
                $this->storeId = $storeId;
            }

            $filter = $this->criteriaBuilder->addFilter('status', 'preparing')
                ->addFilter('store_id', $this->storeId)
                ->create();

            $searchResult = $this->orderRepository->getList($filter);
            $totalRecord = 0;
            $items = $searchResult->getItems();

            if ($items === null) {
                throw new NotFoundException(__('Order not found!'));
            }

            foreach ($items as $item) {
                $oldStatus = $item->getStatus();
                $item->setStatus('delivery_complete')->setState('complete');
                try {
                    $this->orderRepository->save($item);
                    $output->writeln(__("Updated an order status: '%1' to '%2'. Order Id: #%3",
                        strtoupper($oldStatus),
                        strtoupper($item->getStatus()),
                        $item->getIncrementId()));
                    $totalRecord++;
                } catch(\Exception $e) {
                    $output->writeln($e->getMessage());
                }
            }

            $output->writeln(__('Total orders have been updated status: %1', $totalRecord));

        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage());
        }
    }
}
