<?php

namespace Ipay88\Payment\Controller\Checkout;

class Callback extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpPostActionInterface,
                                                                       \Magento\Framework\App\CsrfAwareActionInterface
{
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $magentoCache;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $magentoRequest;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $magentoApiSearchCriteriaBuilder;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $magentoDbTransactionFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $magentoSalesOrderRepository;

    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    protected $magentoSalesOrderManagement;

    /**
     * @var \Magento\Sales\Api\InvoiceOrderInterface
     */
    protected $magentoSalesInvoiceOrder;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $magentoSalesOrderConfig;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $magentoSalesInvoiceService;

    /**
     * @var \Magento\Sales\Model\Order\InvoiceRepository
     */
    protected $magentoSalesInvoiceRepository;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $magentoSalesInvoiceSender;

    /**
     * @var \Ipay88\Payment\Helper\Data
     */
    protected $ipay88PaymentDataHelper;

    /**
     * @var \Ipay88\Payment\Logger\Logger
     */
    protected $ipay88PaymentLogger;

    /**
     * Callback constructor.
     *
     * @param  \Magento\Framework\App\Action\Context  $context
     * @param  \Magento\Framework\App\CacheInterface  $magentoCache
     * @param  \Magento\Framework\Api\SearchCriteriaBuilder  $magentoApiSearchCriteriaBuilder
     * @param  \Magento\Framework\DB\TransactionFactory  $magentoDbTransactionFactory
     * @param  \Magento\Sales\Api\OrderRepositoryInterface  $magentoSalesOrderRepository
     * @param  \Magento\Sales\Api\OrderManagementInterface  $magentoSalesOrderManagement
     * @param  \Magento\Sales\Api\InvoiceOrderInterface  $magentoSalesInvoiceOrder
     * @param  \Magento\Sales\Model\Order\Config  $magentoSalesOrderConfig
     * @param  \Magento\Sales\Model\Order\InvoiceRepository  $magentoSalesInvoiceRepository
     * @param  \Magento\Sales\Model\Service\InvoiceService  $magentoSalesInvoiceService
     * @param  \Magento\Sales\Model\Order\Email\Sender\InvoiceSender  $magentoSalesInvoiceSender
     * @param  \Ipay88\Payment\Helper\Data  $ipay88PaymentDataHelper
     * @param  \Ipay88\Payment\Logger\Logger  $ipay88PaymentLogger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\CacheInterface $magentoCache,
        \Magento\Framework\Api\SearchCriteriaBuilder $magentoApiSearchCriteriaBuilder,
        \Magento\Framework\DB\TransactionFactory $magentoDbTransactionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $magentoSalesOrderRepository,
        \Magento\Sales\Api\OrderManagementInterface $magentoSalesOrderManagement,
        \Magento\Sales\Api\InvoiceOrderInterface $magentoSalesInvoiceOrder,
        \Magento\Sales\Model\Order\Config $magentoSalesOrderConfig,
        \Magento\Sales\Model\Order\InvoiceRepository $magentoSalesInvoiceRepository,
        \Magento\Sales\Model\Service\InvoiceService $magentoSalesInvoiceService,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $magentoSalesInvoiceSender,
        \Ipay88\Payment\Helper\Data $ipay88PaymentDataHelper,
        \Ipay88\Payment\Logger\Logger $ipay88PaymentLogger
    ) {
        parent::__construct($context);

        $this->magentoCache                    = $magentoCache;
        $this->magentoRequest                  = $context->getRequest();
        $this->magentoApiSearchCriteriaBuilder = $magentoApiSearchCriteriaBuilder;
        $this->magentoDbTransactionFactory     = $magentoDbTransactionFactory;
        $this->magentoSalesOrderRepository     = $magentoSalesOrderRepository;
        $this->magentoSalesOrderManagement     = $magentoSalesOrderManagement;
        $this->magentoSalesInvoiceOrder        = $magentoSalesInvoiceOrder;
        $this->magentoSalesOrderConfig         = $magentoSalesOrderConfig;
        $this->magentoSalesInvoiceService      = $magentoSalesInvoiceService;
        $this->magentoSalesInvoiceRepository   = $magentoSalesInvoiceRepository;
        $this->magentoSalesInvoiceSender       = $magentoSalesInvoiceSender;
        $this->ipay88PaymentDataHelper         = $ipay88PaymentDataHelper;
        $this->ipay88PaymentLogger             = $ipay88PaymentLogger;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(): void
    {
        $responseData = $this->ipay88PaymentDataHelper->normalizeResponseData($this->magentoRequest->getParams());

        $salesOrder = $this->getOrderFromResponse($responseData);

        // check if order exist
        if ( ! $salesOrder) {
            $this->handleError(__("No order #{$responseData['ref_no']} for processing found."), $responseData);

            return;
        }

        // restore cart and cancel order if signature empty
        $isResponseSignatureExists = $this->ipay88PaymentDataHelper->isResponseSignatureExist($responseData);
        if ( ! $isResponseSignatureExists) {
            $this->handleEmptyResponseSignature($salesOrder, $responseData);

            return;
        }

        // ignore request if signature mismatches
        $isResponseSignatureMatched = $this->ipay88PaymentDataHelper->isResponseSignatureMatched($responseData);
        if ( ! $isResponseSignatureMatched) {
            $this->handleError(__("Returned signature `{$responseData['signature']}` not match."), $responseData);

            return;
        }


        if ($responseData['status'] === \Ipay88\Payment\Gateway\Config\Config::PAYMENT_STATUS_FAIL) {
            $this->handleError($responseData['err_desc'], $responseData, $salesOrder);

            return;
        }

        if ($responseData['status'] === \Ipay88\Payment\Gateway\Config\Config::PAYMENT_STATUS_SUCCESS) {
            $this->handleSuccessResponse($salesOrder, $responseData);

            return;
        }

        $this->handleNoHandler($salesOrder, $responseData);
    }

    /**
     * @param  array  $response
     *
     * @return \Magento\Sales\Model\Order|false
     */
    protected function getOrderFromResponse(array $response)
    {
        $salesOrderSearchCriteria = $this->magentoApiSearchCriteriaBuilder->addFilter(
            \Magento\Sales\Api\Data\OrderInterface::INCREMENT_ID,
            $response['ref_no']
        )->create();

        $salesOrderCollection = $this->magentoSalesOrderRepository->getList($salesOrderSearchCriteria)->getItems();

        return reset($salesOrderCollection);
    }

    /**
     * @param  \Magento\Sales\Model\Order  $salesOrder
     * @param  array  $response
     */
    protected function handleEmptyResponseSignature(
        \Magento\Sales\Model\Order $salesOrder,
        array $response
    ): void {
        $this->handleError(__($response['err_desc']), $response, $salesOrder);

        $salesOrder->addStatusToHistory(
            false,
            __('Order cancelled due to response signature is empty. Please check order status in merchant portal for confirmation.')
        );

        $this->magentoSalesOrderRepository->save($salesOrder);
    }

    /**
     * @param  \Magento\Sales\Model\Order  $salesOrder
     * @param  array  $response
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function handleSuccessResponse(
        \Magento\Sales\Model\Order $salesOrder,
        array $response
    ): void {
        if ($salesOrder->getPayment()->getLastTransId()) {
            $this->ipay88PaymentLogger->info('[callback] transaction existed', [
                'order'    => $salesOrder->getIncrementId(),
                'response' => $response,
            ]);

            echo 'RECEIVEOK';
            exit;
        }

        $isProcessing = (bool) $this->magentoCache->load("ipay88_payment_processing_{$salesOrder->getIncrementId()}");
        if ($isProcessing) {
            $this->ipay88PaymentLogger->info('[callback] processing by request', [
                'order'    => $salesOrder->getIncrementId(),
                'response' => $response,
            ]);

            sleep(1);

            echo 'RECEIVEOK';
            exit;
        }

        $this->magentoCache->save(1, "ipay88_payment_processing_{$salesOrder->getIncrementId()}");

        $salesInvoice = $this->magentoSalesInvoiceService->prepareInvoice($salesOrder);
        $salesInvoice->setTransactionId($response['trans_id']);
        //            $salesInvoice->setRequestedCaptureCase();
        $salesInvoice->register();

        //            $salesOrder->setCustomerNoteNotify(! empty($data['send_email']));
        $salesOrder->setIsInProcess(true);
        $salesOrder->getPayment()->setLastTransId($response['trans_id']);
        $salesOrder->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
        $salesOrder->setStatus($this->magentoSalesOrderConfig->getStateDefaultStatus($salesOrder->getState()));
        $salesOrder->addStatusToHistory($salesOrder->getStatus(), "Ipay88 transaction #{$salesInvoice->getTransactionId()} success.");

        $dbTransaction = $this->magentoDbTransactionFactory->create();
        $dbTransaction->addObject($salesOrder);
        $dbTransaction->addObject($salesInvoice);
        $dbTransaction->save();

        $this->magentoCache->remove("ipay88_payment_processing_{$salesOrder->getIncrementId()}");

        $this->magentoSalesInvoiceSender->send($salesInvoice);

        $this->ipay88PaymentLogger->info('[callback] success', [
            'order'    => $salesOrder->getIncrementId(),
            'invoice'  => $salesInvoice->getIncrementId(),
            'response' => $response,
        ]);

        echo 'RECEIVEOK';
        exit;
    }

    /**
     * @param  \Magento\Sales\Model\Order  $salesOrder
     * @param  array  $response
     */
    protected function handleNoHandler(
        \Magento\Sales\Model\Order $salesOrder,
        array $response
    ): void {
        $this->ipay88PaymentLogger->notice('[callback] no handler', [
            'order'    => $salesOrder->getIncrementId(),
            'response' => $response,
        ]);
    }

    /**
     * @param  string  $errorMessage
     * @param  array  $responseData
     * @param  \Magento\Sales\Model\Order|null  $salesOrder  cancel order if set
     */
    protected function handleError(
        string $errorMessage,
        array $responseData,
        \Magento\Sales\Model\Order $salesOrder = null
    ): void {
        if ($salesOrder) {
            $this->magentoSalesOrderManagement->cancel($salesOrder->getId());

            $errorContext['order'] = $salesOrder->getIncrementId();
        }

        $errorContext['response'] = $responseData;

        $this->ipay88PaymentLogger->error("[callback] {$errorMessage}", $errorContext);
    }

    /**
     * @param  \Magento\Framework\App\RequestInterface  $request
     *
     * @return \Magento\Framework\App\Request\InvalidRequestException|null
     */
    public function createCsrfValidationException(
        \Magento\Framework\App\RequestInterface $request
    ): ?\Magento\Framework\App\Request\InvalidRequestException {
        return null;
    }

    /**
     * @param  \Magento\Framework\App\RequestInterface  $request
     *
     * @return bool|null
     */
    public function validateForCsrf(
        \Magento\Framework\App\RequestInterface $request
    ): ?bool {
        return true;
    }
}
