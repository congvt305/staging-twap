<?php
declare(strict_types=1);

namespace CJ\Invoice\Console\Command;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Invoice;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InvoiceGenerationCommand extends Command
{
    const OPTION_ORDER_ID = "order_id";
    const OPTION_CAPTURE = "capture";

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param string|null $name
     */
    public function __construct(OrderRepositoryInterface $orderRepository, string $name = null)
    {
        $this->orderRepository = $orderRepository;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orderId = $input->getOption(self::OPTION_ORDER_ID);
        $capture = $input->getOption(self::OPTION_CAPTURE);
        if ($orderId) {
            try {
                $order = $this->orderRepository->get($orderId);
                if ($order->canInvoice()) {
                    $invoice = $order->prepareInvoice();
                    $captureCase = $capture ? Invoice::CAPTURE_ONLINE : Invoice::CAPTURE_OFFLINE;
                    $invoice->setRequestedCaptureCase($captureCase);
                    $invoice->register();
                    $order->addRelatedObject($invoice);
                    $order->setHasForcedCanCreditmemo(true);
                    $order->setForcedCanCreditmemo(true);
                    $this->orderRepository->save($order);
                    $output->writeln("<info>Done!</info>");
                } else {
                    $output->writeln("<info>Cannot create invoice!</info>");
                }
            } catch (\Throwable $exception) {
                $message = $exception->getMessage();
                $output->writeln("<error>$message</error>");
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("cj:invoice:create");
        $this->setDescription("Generate Invoice");
        $this->addOption(
            self::OPTION_ORDER_ID,
            null,
            InputOption::VALUE_REQUIRED,
            'Order Increment Id'
        );
        $this->addOption(
            self::OPTION_CAPTURE,
            null,
            InputOption::VALUE_OPTIONAL,
            'Do Capture?'
        );
        parent::configure();
    }
}
