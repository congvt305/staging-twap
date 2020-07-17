<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/20/20
 * Time: 5:47 PM
 */

namespace Eguana\GWLogistics\Model\Request;

use Eguana\GWLogistics\Model\Lib\EcpayCheckMacValue;
use Eguana\GWLogistics\Model\Lib\EcpayIsCollection;
use Eguana\GWLogistics\Model\Lib\EcpayLogisticsSubType;
use Eguana\GWLogistics\Model\Lib\EcpayLogisticsType;
//use \Magento\Framework\HTTP\Client\CurlFactory; //maybe try later...

class CvsCreateShipmentOrder
{

    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    private $helper;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    private $dateTimeFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Eguana\GWLogistics\Model\Lib\EcpayLogistics
     */
    private $ecpayLogistics;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Eguana\GWLogistics\Model\Lib\EcpayLogistics $ecpayLogistics,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Eguana\GWLogistics\Helper\Data $helper
    ) {
        $this->helper = $helper;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->logger = $logger;
        $this->ecpayLogistics = $ecpayLogistics;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function sendRequest($order) {
        $dataTime = $this->dateTimeFactory->create();
        //request
        //{"MerchantID":"2000132","MerchantTradeNo":"1592653900596","LogisticsSubType":"UNIMART","CVSStoreID":"991182","CVSStoreName":"馥樺門市","CVSAddress":"台北市南港區三重路23號1樓","CVSTelephone":"","CVSOutSide":"0","ExtraData":""}
        $gatewayUrl = 'https://logistics-stage.ecpay.com.tw/Express/Create';
        $merchantId = '2000132';
        $merchantTradeNo = $order->getId() . '_' . $dataTime->date('YmdHis'); //order id
        $merchantTradeDate = $dataTime->date('Y/m/d H:i:s');
        $logisticsType = EcpayLogisticsType::CVS;
        $logisticsSubType = EcpayLogisticsSubType::UNIMART;
        $goodsAmount = (int)round($order->getSubtotal(), 0);
        //Characters are limited to 10 characters (upto 5 Chinese characters, 10 English characters)
        $senderName = 'StoreName'; //no space not more than 10.
        //Character limit is 4-10 characters (Chinese2-5 characters, English 4-10 characters)
        $receiverName = $order->getShippingAddress()->getFirstname() . $order->getShippingAddress()->getLastname();
        $serverReplyURL = $this->helper->getCreateShipmentReplyUrl();
        $receiverStoreID = '991182'; //no need, only for C2C
        $returnStoreID = '991182'; //no need, only for C2C

        //for test, sender name, receiver name receiver phone/cellphone , ReceiverStoreID ReturnStoreID are required....!!

        $params = [
            'MerchantID' => $merchantId,//
            'MerchantTradeNo' => $merchantTradeNo,
            'MerchantTradeDate' => $merchantTradeDate,//
            'LogisticsType' => $logisticsType,//
            'LogisticsSubType' => $logisticsSubType,//
            'GoodsAmount' => $goodsAmount,//
            'CollectionAmount' => 0,
            'IsCollection' => EcpayIsCollection::NO,
            'GoodsName' => '',
            'SenderName' => $senderName, //
            'SenderPhone' => '',
            'SenderCellPhone' => '',
            'ReceiverName' => $receiverName, //
            'ReceiverPhone' => '0226550115', //required for c2c
            'ReceiverCellPhone' => '0933222111',
            'ReceiverEmail' => '',
            'TradeDesc' => '',
            'ServerReplyURL' => $serverReplyURL,//
            'ClientReplyURL' => '',
            'LogisticsC2CReplyURL' => '',
            'Remark' => '',
            'PlatformID' => '',
        ];

        try {
            $this->ecpayLogistics->ServiceURL = 'https://logistics-stage.ecpay.com.tw/Express/Create';
            $this->ecpayLogistics->HashKey = '5294y06JbISpM5x9';
            $this->ecpayLogistics->HashIV = 'v77hoKGq4kWxNNIS';
            $this->ecpayLogistics->Send = $params;
            $this->ecpayLogistics->SendExtend = [
                'ReceiverStoreID' => $receiverStoreID, //cvs store id from map request
                'ReturnStoreID' => $returnStoreID //need to
            ];
            $result = $this->ecpayLogistics->BGCreateShippingOrder();
            $this->logger->debug('GWL create shipment result: ', $result);
            return $result;
        } catch (\Exception $e) {
            $this->logger->critical('GWL create shipment failed');
            $this->logger->critical($e->getMessage());
        }
    }

}
