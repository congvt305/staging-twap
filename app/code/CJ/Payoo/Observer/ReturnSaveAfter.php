<?php

namespace CJ\Payoo\Observer;

use Magento\Framework\Event\ObserverInterface;
use Payoo\PayNow\Logger\Logger as PayooLogger;
use Magento\Sales\Api\OrderRepositoryInterface;
use Payoo\PayNow\Model\Ui\ConfigProvider;
use Magento\Rma\Model\Rma\Source\Status;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\Sales\Model\Order\CreditmemoFactory;
use Magento\Sales\Model\Order\Invoice;
use Exception;

class ReturnSaveAfter implements ObserverInterface
{
    /**
     * @var CreditmemoService
     */
    protected CreditmemoService $creditmemoService;

    /**
     * @var CreditmemoFactory
     */
    protected CreditmemoFactory $creditmemoFactory;

    /**
     * @var Invoice
     */
    protected Invoice $invoice;

    /**
     * @var OrderRepositoryInterface
     */
    protected OrderRepositoryInterface $orderRepository;

    /**
     * @var PayooLogger
     */
    protected PayooLogger $logger;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param PayooLogger $logger
     */
    public function __construct(
        CreditmemoService $creditmemoService,
        CreditmemoFactory $creditmemoFactory,
        Invoice $invoice,
        OrderRepositoryInterface $orderRepository,
        PayooLogger $logger
    ) {
        $this->creditmemoService = $creditmemoService;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->invoice = $invoice;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $return = $observer->getRma();
            $order = $this->orderRepository->get($return->getOrderId());
            $payment = $order->getPayment();
            if ($payment->getMethod() == ConfigProvider::CODE && $return->getStatus() == Status::STATE_PROCESSED_CLOSED) {
                $this->logger->info(PayooLogger::TYPE_LOG_RETURN, ['message' => 'Start Return Payoo']);
                $invoice = $order->getInvoiceCollection()->getFirstItem();
                $invoicedata = $this->invoice->loadByIncrementId($invoice->getIncrementId());
                if ($return->getPartialTotalAmount()) {
                    $dataQty['qtys'] = [];
                    foreach($return->getItems() as $item) {
                        $dataQty['qtys'][$item->getOrderItemId()] = $item->getQtyRequested();
                    }
                    $creditmemo = $this->creditmemoFactory->createByOrder($order, $dataQty);
                    $creditmemo->setBaseGrandTotal($return->getPartialTotalAmount());
                    $creditmemo->setBaseSubtotal($return->getPartialTotalAmount());
                } else {
                    $creditmemo = $this->creditmemoFactory->createByOrder($order);
                }
                $creditmemo->setInvoice($invoicedata);
                if (!$order->getTotalRefunded()) {
                    $this->creditmemoService->refund($creditmemo);
                }

            }
        } catch (Exception $exception) {
            $this->logger->error(PayooLogger::TYPE_LOG_RETURN,
                [
                    'message' => $exception->getMessage()
                ]
            );
        }
    }
}
