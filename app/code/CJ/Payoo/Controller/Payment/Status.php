<?php

namespace CJ\Payoo\Controller\Payment;

use Magento\Framework\Controller\ResultFactory;

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
        $session = $this->request->getParam('session');
        $orderNo = $this->request->getParam('order_no');
        $status = $this->request->getParam('status');
        $checksum = $this->request->getParam('checksum');

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $key = $this->scopeConfig->getValue('payment/paynow/checksum_key', $storeScope);
        $cs = hash('sha512', $key . $session . '.' . $orderNo . '.' . $status);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (strtoupper($cs) == strtoupper($checksum)) {
            $order_code = @$_GET['order_no'];

            if ($order_code != '' && $status == 1) {
                $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/test.log');
                $logger = new \Zend_Log();
                $logger->addWriter($writer);
                $logger->info('--' . get_class());
                $logger->info('3. before exec function updateOrderStatus');
                //complete
                $this->UpdateOrderStatus($order_code, $this->config->getStatusPaymentSuccess());
                $logger->info('4. after exec function updateOrderStatus');
                $resultRedirect->setPath('checkout/onepage/success');
                return $resultRedirect;
            } else {
                //canceled
                $this->UpdateOrderStatus($order_code, \Magento\Sales\Model\Order::STATE_CANCELED);
            }
        }

        $resultRedirect->setPath('checkout/cart');
        return $resultRedirect;
    }
}
