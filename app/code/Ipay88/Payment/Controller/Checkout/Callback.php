<?php

namespace Ipay88\Payment\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

class Callback extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpPostActionInterface, \Magento\Framework\App\CsrfAwareActionInterface
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
            $this->handleError(__("No order #{$responseData['ref_no']} for processing found."), $responseData);

            return;
        }

        /**
         * @var \Magento\Sales\Model\Order $salesOrder
         */
        $salesOrder = reset($salesOrderCollection);

        if ($responseData['status'] === \Ipay88\Payment\Gateway\Config\Config::PAYMENT_STATUS_FAIL) {
            $this->handleError($responseData['err_desc'], $responseData, $salesOrder);

            return;
        }

        if ($responseData['status'] === \Ipay88\Payment\Gateway\Config\Config::PAYMENT_STATUS_SUCCESS) {
            $isResponseSignatureValid = $this->ipay88PaymentDataHelper->validateResponseSignature($responseData);
            if ( ! $isResponseSignatureValid) {
                $this->handleError(__("Returned signature `{$responseData['signature']}` not match."), $responseData, $salesOrder);

                return;
            }

            if ($salesOrder->getPayment()->getLastTransId()) {
                $this->ipay88PaymentLogger->info('[callback] transaction existed', [
                    'order'    => $salesOrder->getIncrementId(),
                    'response' => $responseData,
                ]);

                echo 'RECEIVEOK';
                exit;
            }

            $isProcessing = (bool) $this->magentoCache->load("ipay88_payment_processing_{$salesOrder->getIncrementId()}");
            if ($isProcessing) {
                $this->ipay88PaymentLogger->info('[callback] processing by request', [
                    'order'    => $salesOrder->getIncrementId(),
                    'response' => $responseData,
                ]);

                sleep(1);

                echo 'RECEIVEOK';
                exit;
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

            $this->ipay88PaymentLogger->info('[callback] success', [
                'order'    => $salesOrder->getIncrementId(),
                'invoice'  => $salesInvoice->getIncrementId(),
                'response' => $responseData,
            ]);

            echo 'RECEIVEOK';
            exit;
        }

        $this->ipay88PaymentLogger->notice('[callback] no handler', [
            'order'    => $salesOrder->getIncrementId(),
            'response' => $responseData,
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
