<?php

namespace Payoo\PayNow\Controller\Notification;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Index extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    private $invoiceService;
    private $request;
    private $scopeConfig;
    private $transaction;
    private $orderFactory;

    public function __construct(
        Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\Data\OrderInterfaceFactory  $orderFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction
    ) {
        parent::__construct($context);
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->orderFactory = $orderFactory;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
    }

    public function execute()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $message = $this->request->getParam('NotifyData');
        $checksum = $this->scopeConfig->getValue('payment/paynow/checksum_key', $storeScope);
        $ipRequest = $this->scopeConfig->getValue('payment/paynow/environment', $storeScope);
        $response = json_decode(base64_decode($message), true);

        if (strtoupper(hash('sha512',$checksum.$response['ResponseData'].$ipRequest)) != strtoupper($response['Signature'])) {
            $data = json_decode($response['ResponseData'], true);
            $order_code = $data['OrderNo'];
            $status = $data['PaymentStatus'];

            if($order_code != '' && $status == 1)
            {
                //complete
                $this->UpdateOrderStatus($order_code, \Magento\Sales\Model\Order::STATE_COMPLETE);
            }
            else
            {
                //canceled
                $this->UpdateOrderStatus($order_code,\Magento\Sales\Model\Order::STATE_CANCELED);
            }
            echo 'NOTIFY_RECEIVED';
        } else {
            echo "<h3>Listening....</h3>";
        }
    }

    function UpdateOrderStatus($order_no,$status)
	{
		$order = $this->orderFactory->create()->loadByIncrementId($order_no);
        if ($status === \Magento\Sales\Model\Order::STATE_COMPLETE) {
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
        }  
        $order->setStatus($status)->save();
      
        $messsge = $status === \Magento\Sales\Model\Order::STATE_COMPLETE ? 'Payoo Transaction Complete' : 'Payoo Transaction Cancel';
        $order->addStatusHistoryComment(
            __($messsge, $status)
        )
            ->setIsCustomerNotified(true)
            ->save();
	}

    public function createCsrfValidationException(RequestInterface $request): ? InvalidRequestException {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool {
        return true;
    }
}