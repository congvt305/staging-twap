<?php

namespace Payoo\PayNow\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Status extends Action
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
        $session = $this->request->getParam('session');
        $orderNo = $this->request->getParam('order_no');
        $status = $this->request->getParam('status');
        $checksum = $this->request->getParam('checksum');
       
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $key = $this->scopeConfig->getValue('payment/paynow/checksum_key', $storeScope);
        $cs = hash('sha512',$key.$session.'.'.$orderNo.'.'.$status);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
    
        if (strtoupper($cs) == strtoupper($checksum)) {
            $order_code = @$_GET['order_no'];
          
            if($order_code != '' && $status == 1)
            {
                //complete
                $this->UpdateOrderStatus($order_code, \Magento\Sales\Model\Order::STATE_COMPLETE);
                $resultRedirect->setPath('checkout/onepage/success');
                return $resultRedirect;
            }
            else
            {
                //canceled
                $this->UpdateOrderStatus($order_code,\Magento\Sales\Model\Order::STATE_CANCELED);
            }
        }

        $resultRedirect->setPath('checkout/cart');
        return $resultRedirect;
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
}