<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/07/16
 * Time: 3:21 PM
 */

namespace Eguana\EInvoice\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class EInvoiceIssueInvalidObserver implements ObserverInterface
{
    /**
     * @var \Ecpay\Ecpaypayment\Model\Payment
     */
    private $ecpayPaymentModel;

    public function __construct(
        \Ecpay\Ecpaypayment\Model\Payment $ecpayPaymentModel
    ) {
        $this->ecpayPaymentModel = $ecpayPaymentModel;
    }

    public function execute(Observer $observer)
    {
        $payment = $observer->getEvent()->getPayment();
        $eInvoiceData = json_decode($payment->getAdditionalData(), true);

        if (isset($eInvoiceData["InvoiceNumber"]) && ($eInvoiceData["RtnCode"] == 1)) {
            $this->ecpayPaymentModel->invalidateEInvoice($payment, $payment->getOrder()->getStoreId());
        }
    }
}
