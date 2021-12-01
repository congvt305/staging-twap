<?php

namespace CJ\ChangeOrderStatus\Console;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Sales\Model\OrderFactory;
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
     * @var \Magento\Sales\Model\Convert\Order
     */
    protected $convertOrder;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Sales\Model\Convert\Order $convertOrder
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param null $name
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        $name = null
    ) {
        $this->orderFactory = $orderFactory;
        $this->state = $state;
        $this->orderRepository = $orderRepository;
        $this->criteriaBuilder = $searchCriteriaBuilder;
        $this->convertOrder = $convertOrder;
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
            $size = $searchResult->getTotalCount();
            $totalRecord = 0;
            $items = $searchResult->getItems();

            if ($items === null) {
                throw new NotFoundException(__('Order not found!'));
            }

            $output->writeln(__('Found %1 orders', $size));
            foreach ($items as $item) {
                $order = $this->orderFactory->create()->load($item->getEntityId());
                $oldStatus = $item->getStatus();

                try {
                    if (!$order->canShip()) {
                        throw new LocalizedException(__('You can\'t create an shipment. Order Id: #%1',
                            $order->getIncrementId()));
                    }
                    $shipment = $this->convertOrder->toShipment($order);
                    foreach ($order->getAllItems() as $orderItem) {
                        if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                            continue;
                        }

                        $qtyShipped = $orderItem->getQtyToShip();
                        $shipmentItem = $this->convertOrder
                            ->itemToShipmentItem($orderItem)
                            ->setQty($qtyShipped);

                        $shipment->addItem($shipmentItem);
                    }

                    $shipment->register();
                    $shipment->getOrder()
                        ->setStatus('delivery_complete')
                        ->setState('complete')
                        ->save();

                    $shipment->save();
                    $output->writeln(
                        __('Created a shipment successfully. Order Id: #%1. Shipment Id: %2. Order Old Status: %3. New Status: %4',
                        $order->getIncrementId(), $shipment->getIncrementId(), $oldStatus, $shipment->getOrder()->getStatus())
                    );

                    $totalRecord++;
                } catch (\Exception $e) {
                    $output->writeln($e->getMessage());
                }
            }

            $output->writeln(__('Total orders affected: %1', $totalRecord));

        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage());
        }
    }
}
