<?php

namespace Ecpay\Ecpaypayment\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class PaymentController extends Action
{
    protected $_order;
    /**
     * @var \Ecpay\Ecpaypayment\Model\Payment
     */
    private $ecpayPaymentModel;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    public function __construct(
        Context $context,
        \Ecpay\Ecpaypayment\Model\Payment $ecpayPaymentModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->ecpayPaymentModel = $ecpayPaymentModel;
        $this->storeManager = $storeManager;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        try {
            $eInvoiceData = $this->getRequest()->getParams();
            $storeId = $this->storeManager->getStore()->getId();

            $resultJson = $this->resultJsonFactory->create();
            $response = $this->ecpayPaymentModel->checkMobileBarCode($eInvoiceData, $storeId);
            $resultJson->setData($response);

            return $resultJson;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
