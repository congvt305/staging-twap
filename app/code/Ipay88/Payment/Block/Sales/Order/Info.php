<?php

namespace Ipay88\Payment\Block\Sales\Order;

use Ipay88\Payment\Gateway\Config\Config;

class Info extends \Magento\Payment\Block\ConfigurableInfo
{
    /**
     * @var string
     */
    protected $_template = 'Ipay88_Payment::sales/order/info.phtml';

    /**
     * @var string
     */
    protected $paymentType;

    public function getLastTransId()
    {
        return $this->getInfo()->getLastTransId();
    }

    public function getPaymentType()
    {
        if ( ! $this->paymentType) {
            $paymentTypeId = (int) $this->getInfo()->getAdditionalInformation('payment_id');
            if ( ! $paymentTypeId) {
                return;
            }

            foreach (Config::PAYMENT_TYPES as $paymentTypes) {
                foreach ($paymentTypes as $paymentType) {
                    if ($paymentType['id'] === $paymentTypeId) {
                        $this->paymentType = $paymentType['name'];
                        break 2;
                    }
                }
            }
        }

        return $this->paymentType;
    }

}