<?php

namespace Ecpay\Ecpaypayment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class DataAssignObserver extends AbstractDataAssignObserver
{

    const KEY_ECPAY_CHOOSEN_PAYMENT = 'ecpay_choosen_payment';
    const KEY_ECPAY_EINVOICE_TYPE = 'ecpay_einvoice_type';
    const KEY_ECPAY_EINVOICE_TAX_ID_NUMBER = 'ecpay_einvoice_tax_id_number';
    const KEY_ECPAY_EINVOICE_CELLPHONE_BARCODE = 'ecpay_einvoice_cellphone_barcode';

    /**
     * @var array
     */
    private $additionalInformationList = [
        self::KEY_ECPAY_CHOOSEN_PAYMENT,
        self::KEY_ECPAY_EINVOICE_TYPE,
        self::KEY_ECPAY_EINVOICE_TAX_ID_NUMBER,
        self::KEY_ECPAY_EINVOICE_CELLPHONE_BARCODE
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
