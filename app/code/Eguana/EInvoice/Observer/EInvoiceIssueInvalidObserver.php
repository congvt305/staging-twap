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
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $observer->getEvent()->getPayment();
        $order = $payment->getOrder();

        if (!empty($payment->getAdditionalData())) {
            $eInvoiceData = json_decode($payment->getAdditionalData()??'', true);

            if (isset($eInvoiceData["InvoiceNumber"]) && ($eInvoiceData["RtnCode"] == 1)) {
                $this->ecpayPaymentModel->issueAllowance($payment, $order);
            }
        }
    }
}
