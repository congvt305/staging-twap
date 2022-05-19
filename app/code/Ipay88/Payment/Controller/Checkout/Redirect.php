<?php

namespace Ipay88\Payment\Controller\Checkout;

class Redirect extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpPostActionInterface, \Magento\Framework\App\CsrfAwareActionInterface
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
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $magentoResponse;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $magentoResponseRedirect;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $magentoMessageManager;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $magentoViewPageResultFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $magentoApiSearchCriteriaBuilder;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $magentoDbTransactionFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $magentoCheckoutSession;

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
     * @var \Ipay88\Payment\Helper\Data
     */
    protected $ipay88PaymentDataHelper;

    /**
     * @var \Ipay88\Payment\Logger\Logger
     */
    protected $ipay88PaymentLogger;

    /**
     * Redirect constructor.
     *
     * @param  \Magento\Framework\App\Action\Context  $context
     * @param  \Magento\Framework\App\CacheInterface  $magentoCache
     * @param  \Magento\Framework\Api\SearchCriteriaBuilder  $magentoApiSearchCriteriaBuilder
     * @param  \Magento\Framework\DB\TransactionFactory  $magentoDbTransactionFactory
     * @param  \Magento\Checkout\Model\Session  $magentoCheckoutSession
     * @param  \Magento\Sales\Api\OrderRepositoryInterface  $magentoSalesOrderRepository
     * @param  \Magento\Sales\Api\OrderManagementInterface  $magentoSalesOrderManagement
     * @param  \Magento\Sales\Api\InvoiceOrderInterface  $magentoSalesInvoiceOrder
     * @param  \Magento\Sales\Model\Order\Config  $magentoSalesOrderConfig
     * @param  \Magento\Sales\Model\Order\InvoiceRepository  $magentoSalesInvoiceRepository
     * @param  \Magento\Sales\Model\Service\InvoiceService  $magentoSalesInvoiceService
     * @param  \Ipay88\Payment\Helper\Data  $ipay88PaymentDataHelper
     * @param  \Ipay88\Payment\Logger\Logger  $ipay88PaymentLogger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\CacheInterface $magentoCache,
        \Magento\Framework\Api\SearchCriteriaBuilder $magentoApiSearchCriteriaBuilder,
        \Magento\Framework\DB\TransactionFactory $magentoDbTransactionFactory,
        \Magento\Checkout\Model\Session $magentoCheckoutSession,
        \Magento\Sales\Api\OrderRepositoryInterface $magentoSalesOrderRepository,
        \Magento\Sales\Api\OrderManagementInterface $magentoSalesOrderManagement,
        \Magento\Sales\Api\InvoiceOrderInterface $magentoSalesInvoiceOrder,
        \Magento\Sales\Model\Order\Config $magentoSalesOrderConfig,
        \Magento\Sales\Model\Order\InvoiceRepository $magentoSalesInvoiceRepository,
        \Magento\Sales\Model\Service\InvoiceService $magentoSalesInvoiceService,
        \Ipay88\Payment\Helper\Data $ipay88PaymentDataHelper,
        \Ipay88\Payment\Logger\Logger $ipay88PaymentLogger
    ) {
        parent::__construct($context);

        $this->magentoCache                    = $magentoCache;
        $this->magentoRequest                  = $context->getRequest();
        $this->magentoResponse                 = $context->getResponse();
        $this->magentoResponseRedirect         = $context->getRedirect();
        $this->magentoMessageManager           = $context->getMessageManager();
        $this->magentoApiSearchCriteriaBuilder = $magentoApiSearchCriteriaBuilder;
        $this->magentoDbTransactionFactory     = $magentoDbTransactionFactory;
        $this->magentoCheckoutSession          = $magentoCheckoutSession;
        $this->magentoSalesOrderRepository     = $magentoSalesOrderRepository;
        $this->magentoSalesOrderManagement     = $magentoSalesOrderManagement;
        $this->magentoSalesInvoiceOrder        = $magentoSalesInvoiceOrder;
        $this->magentoSalesOrderConfig         = $magentoSalesOrderConfig;
        $this->magentoSalesInvoiceService      = $magentoSalesInvoiceService;
        $this->magentoSalesInvoiceRepository   = $magentoSalesInvoiceRepository;
        $this->ipay88PaymentDataHelper         = $ipay88PaymentDataHelper;
        $this->ipay88PaymentLogger             = $ipay88PaymentLogger;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $responseData = $this->ipay88PaymentDataHelper->normalizeResponseData($this->magentoRequest->getParams());

        $salesOrderSearchCriteria = $this->magentoApiSearchCriteriaBuilder->addFilter(
            \Magento\Sales\Api\Data\OrderInterface::INCREMENT_ID,
            $responseData['ref_no']
        )->create();

        $salesOrderCollection = $this->magentoSalesOrderRepository->getList($salesOrderSearchCriteria)->getItems();
        if ( ! $salesOrderCollection) {
            $this->redirectToCheckoutCartPage(__("No order #{$responseData['ref_no']} for processing found."), $responseData);

            return;
        }

        /**
         * @var \Magento\Sales\Model\Order $salesOrder
         */
        $salesOrder = array_first($salesOrderCollection);

        if ($responseData['status'] === \Ipay88\Payment\Gateway\Config\Config::PAYMENT_STATUS_FAIL) {
            $this->redirectToCheckoutCartPage(__($responseData['err_desc']), $responseData, $salesOrder);

            return;
        }

        if ($responseData['status'] === \Ipay88\Payment\Gateway\Config\Config::PAYMENT_STATUS_PENDING) {
            $this->ipay88PaymentLogger->info('[redirect] pending', [
                'order'    => $salesOrder->getIncrementId(),
                'response' => $responseData,
            ]);

            $this->redirectToCheckoutSuccessPage(__('We have received and will process your order once payment is confirmed.'));

            return;
        }

        if ($responseData['status'] === \Ipay88\Payment\Gateway\Config\Config::PAYMENT_STATUS_SUCCESS) {
            $isResponseSignatureValid = $this->ipay88PaymentDataHelper->validateResponseSignature($responseData);
            if ( ! $isResponseSignatureValid) {
                $this->redirectToCheckoutCartPage(__("Returned signature `{$responseData['signature']}` not match."), $responseData, $salesOrder);

                return;
            }

            if ($salesOrder->getPayment()->getLastTransId()) {
                $this->ipay88PaymentLogger->info('[redirect] transaction existed', [
                    'order'    => $salesOrder->getIncrementId(),
                    'response' => $responseData,
                ]);

                $this->redirectToCheckoutSuccessPage();

                return;
            }

            $isProcessing = (bool) $this->magentoCache->load("ipay88_payment_processing_{$salesOrder->getIncrementId()}");
            if ($isProcessing) {
                $this->ipay88PaymentLogger->info('[redirect] processing by callback', [
                    'order'    => $salesOrder->getIncrementId(),
                    'response' => $responseData,
                ]);

                sleep(1);

                $this->redirectToCheckoutSuccessPage();

                return;
            }

            $this->magentoCache->save(1, "ipay88_payment_processing_{$salesOrder->getIncrementId()}");

            $salesInvoice = $this->magentoSalesInvoiceService->prepareInvoice($salesOrder);
            $salesInvoice->setTransactionId($responseData['trans_id']);
            //            $salesInvoice->setRequestedCaptureCase();
            $salesInvoice->register();

            //            $salesOrder->setCustomerNoteNotify(! empty($data['send_email']));
            $salesOrder->setIsInProcess(true);
            $salesOrder->getPayment()->setLastTransId($responseData['trans_id']);
            $salesOrder->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $salesOrder->setStatus($this->magentoSalesOrderConfig->getStateDefaultStatus($salesOrder->getState()));
            $salesOrder->addStatusToHistory($salesOrder->getStatus(), "Ipay88 transaction #{$salesInvoice->getTransactionId()} success.");

            /**
             * @var \Magento\Framework\DB\Transaction $dbTransaction ;
             */
            $dbTransaction = $this->magentoDbTransactionFactory->create();
            $dbTransaction->addObject($salesOrder);
            $dbTransaction->addObject($salesInvoice);
            $dbTransaction->save();

            $this->magentoCache->remove("ipay88_payment_processing_{$salesOrder->getIncrementId()}");

            $this->ipay88PaymentLogger->info('[redirect] success', [
                'order'    => $salesOrder->getIncrementId(),
                'invoice'  => $salesInvoice->getIncrementId(),
                'response' => $responseData,
            ]);

            $this->redirectToCheckoutSuccessPage();

            return;
        }

        $this->ipay88PaymentLogger->notice('[redirect] no handler', [
            'order'    => $salesOrder->getIncrementId(),
            'response' => $responseData,
        ]);

        $this->redirectToHomepage();
    }

    /**
     * @param  string  $errorMessage
     * @param  array  $responseData
     * @param  \Magento\Sales\Model\Order|null  $salesOrder  cancel order if set
     */
    protected function redirectToCheckoutCartPage(
        string $errorMessage,
        array $responseData,
        \Magento\Sales\Model\Order $salesOrder = null
    ): void {
        $isRestored = $this->magentoCheckoutSession->restoreQuote();

        $this->ipay88PaymentLogger->error("[redirect] is quote restored", [$isRestored ? 'yes' : 'no']);

        $this->magentoMessageManager->addErrorMessage($errorMessage);

        if ($salesOrder) {
            $this->magentoSalesOrderManagement->cancel($salesOrder->getId());

            $errorContext['order'] = $salesOrder->getIncrementId();
        }

        $errorContext['response'] = $responseData;

        $this->ipay88PaymentLogger->error("[redirect] {$errorMessage}", $errorContext);

        $this->magentoResponseRedirect->redirect($this->magentoResponse, 'checkout/cart');
    }

    /**
     * @param  null  $successMessage
     */
    protected function redirectToCheckoutSuccessPage(
        $successMessage = null
    ): void {
        //        $this->magentoCheckoutSession->getQuote()->setIsActive(false)->save();

        if ($successMessage) {
            $this->magentoMessageManager->addSuccessMessage($successMessage);
        }

        $this->magentoResponseRedirect->redirect($this->magentoResponse, 'checkout/onepage/success');
    }

    protected function redirectToHomepage(): void
    {
        $this->magentoResponseRedirect->redirect($this->magentoResponse, '/');
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
