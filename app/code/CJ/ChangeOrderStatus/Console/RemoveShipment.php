<?php

namespace CJ\ChangeOrderStatus\Console;

use Magento\Framework\Exception\NotFoundException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\Registry;

class RemoveShipment extends Command
{
    const NAME = 'cj:removeshipment:run';
    const ORDER_ID = 'orderid';

    /**
     * @var OrderFactory
     */
    protected OrderFactory $orderFactory;

    /**
     * @var Registry
     */
    protected Registry $registry;

    /**
     * @param Registry $registry
     * @param OrderFactory $orderFactory
     * @param string|null $name
     */
    public function __construct(
        Registry $registry,
        OrderFactory $orderFactory,
        string $name = null
    ) {
        $this->registry = $registry;
        $this->orderFactory = $orderFactory;
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Remove shipment from order');
        $this->addOption(
            self::ORDER_ID,
            null,
            InputOption::VALUE_REQUIRED, 'Order id'
        );
        parent::configure();
    }

    /**
     * remove shipment from order
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws NotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->registry->register('isSecureArea', true);
        $orderId = $input->getOption(self::ORDER_ID);
        $order = $this->orderFactory->create()->load($orderId);
        $output->writeln("Start remove from order with ID: " . $orderId);

        if ($order->getId() == null) {
            throw new NotFoundException(__('Order not found!'));
        }
        if (!$order->hasShipments()) {
            throw new NotFoundException(__('Order do not have shipment to remove'));
        }
        //delete shipment
        $shipments = $order->getShipmentsCollection();
        foreach ($shipments as $shipment){
            $shipment->delete();
        }
        //set qty shipped to 0
        $items = $order->getAllVisibleItems();
        foreach($items as $item){
            $item->setQtyShipped(0);
            $item->save();
        }
        //set order status to processing
        $order->setState('processing');
        $order->setStatus('processing');
        $order->save();

        $output->writeln("All Process Done");
    }
}
