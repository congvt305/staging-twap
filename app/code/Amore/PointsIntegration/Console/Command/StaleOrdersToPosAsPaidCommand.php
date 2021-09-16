<?php

namespace Amore\PointsIntegration\Console\Command;

use Amore\PointsIntegration\Model\PosStaleOrderSender;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SomeCommand
 */
class StaleOrdersToPosAsPaidCommand extends Command
{
    const INCREMENT_IDS = 'increment_ids';

    const DEFAULT_ORDERS = ['4000000117',
        '4000000135',
        '4000000147',
        '4000000252',
        '4000000291',
        '4000000303',
        '4000000369',
        '4000000384',
        '4000000390',
        '4000000414',
        '4000000423',
        '4000000423',
        '4000000441',
        '4000000483',
        '4000000492',
        '4000000495',
        '4000000513',
        '4000000522',
        '4000000534',
        '4000000552',
        '4000000558',
        '4000000567',
        '4000000573',
        '4000000576',
        '4000000582',
        '4000002760',
        '4000002877',
    ];

    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var PosStaleOrderSender
     */
    private $posStaleOrderSender;

    /**
     * @param CollectionFactory $orderCollectionFactory
     * @param PosStaleOrderSender $posStaleOrderSender
     * @param string|null $name
     */
    public function __construct(
        CollectionFactory   $orderCollectionFactory,
        PosStaleOrderSender $posStaleOrderSender,
        string              $name = null
    )
    {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->posStaleOrderSender = $posStaleOrderSender;
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('pos:paid:stale');
        $this->setDescription('This command is used to sync completed orders to POS as paid orders');
        $this->addOption(
            self::INCREMENT_IDS,
            null,
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
            'Name'
        );

        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $totalSucceedOrder = 0;
        $incrementIds = $input->getOption(self::INCREMENT_IDS) ?: self::DEFAULT_ORDERS;
        $orders = $this->getOrders($incrementIds);
        foreach ($orders as $order) {
            try {
                $incrementId = $order->getIncrementId();
                $output->writeln("<info>Sending Order $incrementId...</info>");
                $this->posStaleOrderSender->send($order);
                $output->writeln("<info>Done!</info>");
                $totalSucceedOrder++;
            } catch (\Exception $exception) {
                $message = $exception->getMessage();
                $output->writeln("<error>$message</error>");
            } catch (\Throwable $exception) {
                $message = $exception->getMessage();
                $output->writeln("<error>$message</error>");
            }
        }

        $output->writeln("<info>Total succeed order is $totalSucceedOrder</info>");
    }

    /**
     * @param array $incrementIds
     * @return array
     */
    private function getOrders(array $incrementIds): array
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection
            ->addFieldToFilter('increment_id', ['in' => $incrementIds])
            ->addFieldToFilter('pos_order_paid_sent', false);

        return $orderCollection->getItems();
    }
}
