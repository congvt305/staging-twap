<?php

namespace CJ\Payoo\Controller\Notification;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Sales\Model\Order\Payment\Transaction;
use Payoo\PayNow\Logger\Logger as PayooLogger;
use Payoo\PayNow\Model\Payment;

class Index extends \Payoo\PayNow\Controller\Notification\Index
{
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    private $invoiceService;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    private $transaction;
    /**
     * @var \Magento\Sales\Api\Data\OrderInterfaceFactory
     */
    private $orderFactory;
    /**
     * @var \CJ\Payoo\Helper\Data
     *
     */
    private $config;
    /**
     * @var  \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;

    /**
     * @var PayooLogger
     */
    private PayooLogger $payooLogger;

    /**
     * @var Payment
     */
    private Payment $payment;
    /**
     * Const Payoo success status
     */
    const SUCCESS_STATUS = 1;

    /**
     * Const Payoo Payment check sum key
     */
    const PAYOO_PAYMENT_CHECK_SUM_KEY_URL = 'payment/paynow/checksum_key';

    /**
     * Const Payoo Payment environment
     */
    const PAYOO_PAYMENT_ENVIRONMENT_URL = 'payment/paynow/environment';


    /**
     * @param Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\Transaction $transaction
     * @param \CJ\Payoo\Helper\Data $config
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param PayooLogger $payooLogger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\Data\OrderInterfaceFactory  $orderFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \CJ\Payoo\Helper\Data $config,
        \Magento\Framework\Serialize\Serializer\Json $json,
        PayooLogger $payooLogger,
        Payment $payment
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->orderFactory = $orderFactory;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->config = $config;
        $this->json = $json;
        $this->payooLogger = $payooLogger;
        $this->payment = $payment;
        parent::__construct($context, $request, $scopeConfig, $orderFactory, $invoiceService, $transaction);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $message = $this->request->getParam('NotifyData');
        $checksum = $this->scopeConfig->getValue(self::PAYOO_PAYMENT_CHECK_SUM_KEY_URL, $storeScope);
        $ipRequest = $this->scopeConfig->getValue(self::PAYOO_PAYMENT_ENVIRONMENT_URL, $storeScope);
        $response = $this->json->unserialize(base64_decode($message), true);
        $this->payooLogger->info(PayooLogger::TYPE_LOG_CREATE, ['request_notification' => $this->request->getParams()]);
        if(isset($response['ResponseData']) && ($checksum . $response['ResponseData'] . $ipRequest !== null) && isset($response['Signature'])) {
            if (strtoupper(hash('sha512', $checksum . $response['ResponseData'] . $ipRequest)) == strtoupper($response['Signature'])) {
                $order_code = '';
                $status = '';
                $data = $this->json->unserialize($response['ResponseData'], true);
                if(isset($data['OrderNo'])) {
                    $order_code = $data['OrderNo'];
                }
                if(isset($data['PaymentStatus'])) {
                    $status = $data['PaymentStatus'];
                }

                if ($order_code != '' && $status == self::SUCCESS_STATUS) {
                    //complete
                    $this->updateOrderStatusCustom($order_code, $this->config->getPaymentSuccessStatus(), $response);
                } else {
                    //canceled
                    $this->updateOrderStatusCustom($order_code, \Magento\Sales\Model\Order::STATE_CANCELED);
                }
                echo 'NOTIFY_RECEIVED';
            } else {
                echo "<h3>Listening....</h3>";
            }
        } else {
            $this->payooLogger->error(PayooLogger::TYPE_LOG_CREATE, ['Payoo Response:' => $response]);
        }
    }

    /**
     * @param $order_no
     * @param $status
     * @param $response
     * @return void
     */
    function updateOrderStatusCustom($order_no, $status, array $response = [])
    {
        try {
            $order = $this->orderFactory->create()->loadByIncrementId($order_no);
            $statusPaymentSuccess = $this->config->getPaymentSuccessStatus();
            if ((string)$status === (string)$statusPaymentSuccess) {
                $this->payooLogger->info(PayooLogger::TYPE_LOG_CREATE, ['request_notification' => 'Start Create Invoice']);
                if(!$order->hasInvoices()) {
                    $invoice = $this->invoiceService->prepareInvoice($order);
                    $invoice->setTransactionId($order_no);
                    $invoice->register();
                    $invoice->pay();

                    $transactionSave = $this->transaction->addObject(
                        $invoice
                    )->addObject(
                        $invoice->getOrder()
                    );
                    $transactionSave->save();
                    $this->payment->createTransaction($order, $order_no, Transaction::TYPE_CAPTURE, $response);
                    $this->payooLogger->info(PayooLogger::TYPE_LOG_CREATE, ['request_notification' => 'Create Invoice Success']);
                }
                $order->setState($status);
                $message = 'Payoo Transaction Complete';
            }
            else {
                $message = 'Payoo Transaction Cancel';
            }
            $order->setStatus($status)->save();
            $order->addStatusHistoryComment(
                __($message, $status)
            )
                ->setIsCustomerNotified(true)
                ->save();
        } catch (\Exception $exception) {
            $this->payooLogger->error(PayooLogger::TYPE_LOG_CREATE, ['request_notification' => $exception->getMessage()]);
        }
    }
}
