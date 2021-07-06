<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 1/6/21
 * Time: 10:15 PM
 */
namespace Eguana\CODInvoice\Observer;

use Eguana\CODInvoice\Helper\ConfigData;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Invoice;
use Magento\Framework\DB\TransactionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class OrderObserver
 *
 * Class to create invoice when payment through COD
 */
class CreateInvoiceOnCOD implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    private $invoiceCollectionFactory;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ConfigData
     */
    private $configHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ConfigData $configHelper
     * @param InvoiceService $invoiceService
     * @param LoggerInterface $logger
     * @param CollectionFactory $invoiceCollectionFactory
     * @param TransactionFactory $transactionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(
        ConfigData $configHelper,
        InvoiceService $invoiceService,
        LoggerInterface $logger,
        CollectionFactory $invoiceCollectionFactory,
        TransactionFactory $transactionFactory,
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->logger = $logger;
        $this->configHelper = $configHelper;
        $this->invoiceService = $invoiceService;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->transactionFactory = $transactionFactory;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
    }

    /**
     * To create invoice when payment through COD in Laneige Vietname Site
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $orderId = $observer->getEvent()->getOrder()->getId();
        if ($this->configHelper->getEventEnabled()) {
            $this->createInvoice($orderId);
        }
    }

    /**
     * To create invoice when payment through COD
     *
     * @param $orderId
     * @return InvoiceInterface|Invoice|null
     */
    private function createInvoice($orderId)
    {
        try {
            $order = $this->orderRepository->get($orderId);
            if ($order) {
                $paymentMethod = $order->getPayment()->getMethod();
                if ($paymentMethod != 'cashondelivery') {
                    return null;
                }

                $invoices = $this->invoiceCollectionFactory->create()
                    ->addAttributeToFilter('order_id', ['eq' => $order->getId()]);

                $invoices->getSelect()->limit(1);

                if ((int)$invoices->count() !== 0) {
                    $invoices = $invoices->getFirstItem();
                    $invoice = $this->invoiceRepository->get($invoices->getId());
                    return $invoice;
                }

                if (!$order->canInvoice()) {
                    return null;
                }

                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $invoice->getOrder()->setIsInProcess(true);
                $order->addStatusHistoryComment(__('COD Automatically INVOICED'), false);
                $transactionSave = $this->transactionFactory->create()
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $transactionSave->save();

                return $invoice;
            }
        } catch (\Exception $exception) {
            $this->logger->info('COD Invoice error');
            $this->logger->info($exception->getMessage());
        }
    }
}
