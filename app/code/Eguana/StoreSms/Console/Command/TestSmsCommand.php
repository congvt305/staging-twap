<?php

namespace Eguana\StoreSms\Console\Command;

use Eguana\StoreSms\Helper\Data;
use Eguana\StoreSms\Model\SmsSender;
use Magento\Framework\App\State;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zend_Log;
use Zend_Log_Writer_Stream;

class TestSmsCommand extends Command
{
    private const ORDER = 'order';

    private $orderFactory;

    /**
     * @var Data
     */
    private $data;

    /**
     * @var SmsSender
     */
    private $sendNotification;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var State
     */
    private $state;

    public function __construct(
        OrderFactory          $orderFactory,
        Data                  $data,
        SmsSender             $sendNotification,
        StoreManagerInterface $storeManager,
        State $state,
        string                $name = null
    )
    {
        $this->orderFactory = $orderFactory;
        $this->data = $data;
        $this->sendNotification = $sendNotification;
        $this->storeManager = $storeManager;
        $this->state = $state;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('order:sms:send');
        $this->setDescription('Test Order sms command');
        $this->addOption(
            self::ORDER,
            null,
            InputOption::VALUE_REQUIRED,
            'Order ID'
        );

        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

        if ($orderId = $input->getOption(self::ORDER)) {
            $output->writeln('<info>ORDER ID is `' . $orderId . '`</info>');
            $order = $this->orderFactory->create()->load($orderId);
            if ($order->getId()) {
                $output->writeln('<info>Shipping method is `' . $order->getShippingMethod() . '`</info>');
                $output->writeln('<info>Order Status:  `' . $order->getStatus() . '`</info>');

                $storeId = $order->getData('store_id');
                $storeName = $this->data->getStoreName($storeId);
                $newStatus = 'complete';
                $telephone = '0812345678';
                $customerName = 'test customer';
                $orderIncrementId = $order->getIncrementId();
                $templateIdentifer = $this->data->getTemplateIdentifer($newStatus, $storeId);
                $orderNotification = $this->sendNotification
                    ->getOrderNotification($storeId, $templateIdentifer, $customerName, $orderIncrementId, $storeName, $telephone);

                $output->writeln('<info>Template:  `' . $templateIdentifer . '`</info>');
                $output->writeln('<info>Message:  `' . $orderNotification . '`</info>');
            }
        } else {
            $output->writeln('<info>Order is required</info>');
        }
        return 1;
    }
}
