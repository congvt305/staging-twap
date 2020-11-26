<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/11/20
 * Time: 3:57 PM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Gateway\Validator;

use Magento\Framework\Phrase;

class CreateShipmentValidator extends \Eguana\GWLogistics\Model\Gateway\Validator\AbstractValidator
{
    public function validate(array $validateSubject)
    {
        $isValid = false;
        if (isset($validateSubject['ResCode']) &&
            $validateSubject['ResCode'] === '1' &&
            isset($validateSubject['AllPayLogisticsID'])) {
            $isValid = true;
            return $this->createResult($isValid);
        }
        //response for create order {"ResCode":"1","AllPayLogisticsID":"14256261","BookingNote":"","CheckMacValue":"2C22CEC495A329DCFC08F9AABE2FC8D9","CVSPaymentNo":"","CVSValidationNo":"","GoodsAmount":"1595","LogisticsSubType":"FAMI","LogisticsType":"CVS","MerchantID":"3210271","MerchantTradeNo":"4000000066","ReceiverAddress":"","ReceiverCellPhone":"0966221403","ReceiverEmail":"bonglee@tw.amorepacific.com","ReceiverName":"李奉炯","ReceiverPhone":"0966221403","RtnCode":"300","RtnMsg":"訂單處理中(已收到訂單資料)","UpdateStatusDate":"2020/08/28 17:03:24"} []
        $failsDescription = new Phrase($validateSubject['RtnMsg']);
        $errorCode = $validateSubject['ResCode'];

        return $this->createResult($isValid, [$failsDescription], [$errorCode]);
    }
}
