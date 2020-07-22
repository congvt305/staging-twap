<?php

namespace Ecpay\Ecpaypayment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class DataAssignObserver extends AbstractDataAssignObserver
{

    const KEY_ECPAY_CHOOSEN_PAYMENT = 'ecpay_choosen_payment';
    const KEY_ECPAY_EINVOICE_DONATION = 'ecpay_einvoice_donation';
    const KEY_ECPAY_EINVOICE_TITLE = 'ecpay_einvoice_title';
    const KEY_ECPAY_EINVOICE_TAX_ID_NUMBER = 'ecpay_einvoice_tax_id_number';

    /**
     * @var array
     */
    private $additionalInformationList = [
        self::KEY_ECPAY_CHOOSEN_PAYMENT,
        self::KEY_ECPAY_EINVOICE_DONATION,
        self::KEY_ECPAY_EINVOICE_TITLE,
        self::KEY_ECPAY_EINVOICE_TAX_ID_NUMBER
    ];

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (isset($additionalData[$additionalInformationKey])) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            }
        }
    }
}
