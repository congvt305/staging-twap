<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/5/20
 * Time: 8:24 AM
 */

namespace Eguana\CustomerRefund\Model;


class Refund
{
    /**
     * @var \Eguana\CustomerRefund\Helper\Data
     */

    private $dataHelper;

    public function __construct(
        \Eguana\CustomerRefund\Helper\Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */

    public function canRefundOnline($order)
    {
        if ($order->getStatus() === 'processing' && $this->isActive()) {
            if ($order->getPayment()->getMethod() === 'checkmo') { //todo: for test purpose only, should remove later
                return true;
            }

            $ecpayMethod = $this->getEcpayMethod($order);
            if ($ecpayMethod === 'credit') {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $order
     * @return bool|string
     */
    private function getEcpayMethod($order)
    {
        $paymentInfo = $order->getPayment()->getAdditionalInformation();
        if (isset($paymentInfo['ecpay_choosen_payment'])) {
            return $paymentInfo['ecpay_choosen_payment'];
        }

        if (isset($paymentInfo['raw_details_info']['ecpay_choosen_payment'])) {
            return $paymentInfo['raw_details_info']['ecpay_choosen_payment'];
        }

        return false;

    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function canRefundOffline($order)
    {
        if ($order->getStatus() === 'processing' && $this->isActive()) {
            if ($order->getPayment()->getMethod() === 'checkmo') { //todo: for test purpose only, should remove later
                return true;
            }

            $ecpayMethod = $this->getEcpayMethod($order);
            if ($ecpayMethod === 'webatm') {
                return true;
            }
        }
        return false;
    }

    private function isActive()
    {
        return $this->dataHelper->isEnabled();
    }

}
