<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/12/20
 * Time: 10:06 AM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Gateway\Validator;

use Magento\Framework\Phrase;

class QueryLogisticsInfoValidator extends \Eguana\GWLogistics\Model\Gateway\Validator\AbstractValidator
{
//response for query order {"AllPayLogisticsID":"14256261","BookingNote":"","GoodsAmount":"1595","GoodsName":"LaneigeItemX4","HandlingCharge":"52","LogisticsStatus":"300","LogisticsType":"CVS_FAMI","MerchantID":"3210271","MerchantTradeNo":"4000000066","ShipmentNo":"14256261","TradeDate":"2020/08/28 09:03:24","CheckMacValue":"54658955CA33538A7F62AE230797B068"} []
    public function validate(array $validateSubject)
    {
        $isValid = false;
        if (isset($validateSubject['LogisticsStatus'], $validateSubject['ShipmentNo']) &&
            $validateSubject['LogisticsStatus'] === '300') {
            $isValid = true;
            return $this->createResult($isValid);
        }
        $failsDescription = new Phrase(__('Please check shipment order status.'));
        $errorCode = $validateSubject['LogisticsStatus'];

        return $this->createResult($isValid, [$failsDescription], [$errorCode]);
    }

}

