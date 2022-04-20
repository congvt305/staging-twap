<?php

namespace CJ\Payoo\Controller\Payment;

use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Status
 */
class Status extends \Payoo\PayNow\Controller\Payment\Status
{
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $transaction;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterfaceFactory
     */
    protected $orderFactory;

    /**
     * @var \CJ\Payoo\Helper\Data
     */
    protected $config;

    const SUCCESS_STATUS = 1;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \CJ\Payoo\Helper\Data $config
     * @param \Magento\Framework\DB\Transaction $transaction
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \CJ\Payoo\Helper\Data $config,
        \Magento\Framework\DB\Transaction $transaction
    ) {
        $this->config = $config;
        $this->request = $request;
        $this->invoiceService = $invoiceService;
        $this->scopeConfig = $scopeConfig;
        $this->transaction = $transaction;
        parent::__construct($context, $request, $scopeConfig, $orderFactory, $invoiceService, $transaction);
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $session = $this->request->getParam('session', '');
        $orderCode = $this->request->getParam('order_no', '');
        $status = $this->request->getParam('status', '');
        $checksum = $this->request->getParam('checksum', '');

        $key = $this->scopeConfig->getValue('payment/paynow/checksum_key', ScopeInterface::SCOPE_STORE);
        $cs = hash('sha512', $key . $session . '.' . $orderCode . '.' . $status);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (strtoupper($cs) == strtoupper($checksum)) {
            if ($orderCode != '' && $status == self::SUCCESS_STATUS) {
                //complete
                $this->UpdateOrderStatus($orderCode, $this->config->getPaymentSuccessStatus());
                $resultRedirect->setPath('checkout/onepage/success');
                return $resultRedirect;
            } else {
                //canceled
                $this->UpdateOrderStatus($orderCode, \Magento\Sales\Model\Order::STATE_CANCELED);
            }
        }

        $resultRedirect->setPath('checkout/cart');
        return $resultRedirect;
    }

    /**
     * {@inheritDoc}
     */
    function UpdateOrderStatus($order_no, $status)
    {
        $order = $this->orderFactory->create()->loadByIncrementId($order_no);
        $statusPaymentSuccess = $this->config->getPaymentSuccessStatus();
        if ($status === $statusPaymentSuccess) {
            if (!$order->hasInvoices()) {
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->register();
                $invoice->pay();

                $transactionSave = $this->transaction->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );
                $transactionSave->save();
            }
            $order->setState($status);
            $message = 'Payoo Transaction Complete';
        } else {
            $message = 'Payoo Transaction Cancel';
        }
        $order->setStatus($status)->save();
        $order->addStatusHistoryComment(
            __($message, $status)
        )
            ->setIsCustomerNotified(true)
            ->save();
    }
}
