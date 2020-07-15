<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/15/20
 * Time: 4:45 AM
 */

namespace Eguana\GWLogistics\Model\Request;

class CvsCreateReverseShipmentOrder
{
    const  GATEWAY_URL_UNIMART = 'https://logistics-stage.ecpay.com.tw/express/ReturnUniMartCVS';
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Eguana\GWLogistics\Model\Lib\EcpayLogistics
     */
    private $ecpayLogistics;
    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    private $helper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Eguana\GWLogistics\Model\Lib\EcpayLogistics $ecpayLogistics,
        \Eguana\GWLogistics\Helper\Data $helper
    ) {
        $this->logger = $logger;
        $this->ecpayLogistics = $ecpayLogistics;
        $this->helper = $helper;
    }

    public function sendRequest($rma)
    {
        //request
        $gatewayUrl = 'https://logistics-stage.ecpay.com.tw/Express/Create';
        $merchantId = '2000132';
        $serverReplyURL = $this->helper->getReverseLogisticsOrderReplyUrl();
        $goodsName = 'Test item 1'; //50
//        $goodsAmount = (int)round($order->getSubtotal(), 0);
        $goodsAmount = 1000;
        $senderName = '測試寄件者'; //no space not more than 10. return sender name
        $senderPhone = '0226550115'; // return sender phone 20
        $platformId = '0933222111';

        $params = [
            'MerchantID' => $merchantId,
            'AllPayLogisticsID' => '15624',
            'ServerReplyURL' => $serverReplyURL,
            'GoodsName' => $goodsName,
            'GoodsAmount' => $goodsAmount,
            'CollectionAmount' => 0, //
            'ServiceType' => '4',
            'SenderName' => $senderName, //
            'SenderPhone' => $senderPhone, //
            'Remark' => 'test remark',
            'PlatformID' => $platformId,
        ];

        try {
//            $this->ecpayLogistics->ServiceURL = self::GATEWAY_URL_UNIMART;
            $this->ecpayLogistics->HashKey = '5294y06JbISpM5x9';
            $this->ecpayLogistics->HashIV = 'v77hoKGq4kWxNNIS';
            $this->ecpayLogistics->Send = $params;
            $result = $this->ecpayLogistics->CreateUnimartB2CReturnOrder();
            $this->logger->debug('GWL create reverse logistic order result: ', $result);
            return $result; //RtnMerchantTradeNo | RtnOrderNo or |ErrorMessage result array
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

}
