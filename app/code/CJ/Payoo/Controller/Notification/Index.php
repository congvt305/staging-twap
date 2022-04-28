<?php

namespace CJ\Payoo\Controller\Notification;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

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
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\Data\OrderInterfaceFactory  $orderFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \CJ\Payoo\Helper\Data $config,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->orderFactory = $orderFactory;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->config = $config;
        $this->json = $json;
        $this->logger = $logger;
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

        if(isset($response['ResponseData']) && ($checksum . $response['ResponseData'] . $ipRequest !== null) && isset($response['Signature'])) {
            if (strtoupper(hash('sha512', $checksum . $response['ResponseData'] . $ipRequest)) != strtoupper($response['Signature'])) {
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
                    $this->UpdateOrderStatus($order_code, $this->config->getPaymentSuccessStatus());
                } else {
                    //canceled
                    $this->UpdateOrderStatus($order_code, \Magento\Sales\Model\Order::STATE_CANCELED);
                }
                echo 'NOTIFY_RECEIVED';
            } else {
                echo "<h3>Listening....</h3>";
            }
        } else {
            $this->logger->error("Payoo Response: " . $response);
        }
    }

    /**
     * Update Order Status
     *
     * @param $order_no
     * @param $status
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    function UpdateOrderStatus($order_no, $status)
    {
        $order = $this->orderFactory->create()->loadByIncrementId($order_no);
        $statusPaymentSuccess = $this->config->getPaymentSuccessStatus();
        if ((string)$status === (string)$statusPaymentSuccess) {
            if(!$order->hasInvoices()) {
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
    }
}
