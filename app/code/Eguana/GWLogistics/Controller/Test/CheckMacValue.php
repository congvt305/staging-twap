<?php
/**
 * Created by Eguana Team.
 * User =>  sonia
 * Date =>  8/8/20
 * Time =>  5 => 17 PM
 */

namespace Eguana\GWLogistics\Controller\Test;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class CheckMacValue extends Action
{
    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    private $data;

    public function __construct(
        \Eguana\GWLogistics\Helper\Data $data,
        Context $context
    )
    {
        parent::__construct($context);
        $this->data = $data;
    }

    /**
     * Execute action based on request and return result
     *
     * Note =>  Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $params = ["ResCode" => "1","AllPayLogisticsID" => "1631574","BookingNote" => "","CheckMacValue" => "35E32C505ECE96D2B8C419BEFACC96ED","CVSPaymentNo" => "","CVSValidationNo" => "","GoodsAmount" => "200","LogisticsSubType" => "FAMI","LogisticsType" => "CVS","MerchantID" => "2000132","MerchantTradeNo" => "4000000955","ReceiverAddress" => "","ReceiverCellPhone" => "0911222333","ReceiverEmail" => "sodaflash@naver.com","ReceiverName" => "ParkSonia","ReceiverPhone" => "0911222333","RtnCode" => "300","RtnMsg" => "訂單處理中(已收到訂單資料)","UpdateStatusDate" => "2020/08/08 16 => 15 => 27"];
        $valication = $this->data->validateCheckMackValue($params, 4);
        var_dump($valication);
    }
}
