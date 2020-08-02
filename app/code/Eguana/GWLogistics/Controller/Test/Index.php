<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/21/20
 * Time: 8:34 AM
 */

namespace Eguana\GWLogistics\Controller\Test;


use Eguana\GWLogistics\Model\Lib\EcpayIsCollection;
use Eguana\GWLogistics\Model\Lib\EcpayLogisticsSubType;
use Eguana\GWLogistics\Model\Lib\EcpayLogisticsType;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class Index extends Action
{

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
    private $dataHelper;

    public function __construct(
        \Eguana\GWLogistics\Helper\Data $dataHelper,
        \Psr\Log\LoggerInterface $logger,
        \Eguana\GWLogistics\Model\Lib\EcpayLogistics $ecpayLogistics,
        Context $context
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->ecpayLogistics = $ecpayLogistics;
        $this->dataHelper = $dataHelper;
    }

    public function execute()
    {
        $homeUrl = $this->dataHelper->getCreateShipmentReplyUrl();
        try {
            $this->ecpayLogistics->ServiceURL = 'https://logistics-stage.ecpay.com.tw/Express/Create';
            $this->ecpayLogistics->HashKey = '5294y06JbISpM5x9';
            $this->ecpayLogistics->HashIV = 'v77hoKGq4kWxNNIS';
            $this->ecpayLogistics->Send = array(
                'MerchantID' => '2000132',
                'MerchantTradeNo' => 'no' . date('YmdHis'),
                'MerchantTradeDate' => date('Y/m/d H:i:s'),
                'LogisticsType' => EcpayLogisticsType::CVS,
                'LogisticsSubType' => EcpayLogisticsSubType::UNIMART,
                'GoodsAmount' => 1500,
                'CollectionAmount' => 10,
                'IsCollection' => EcpayIsCollection::YES,
                'GoodsName' => '測試商品',
                'SenderName' => '測試寄件者',
                'SenderPhone' => '0226550115',
                'SenderCellPhone' => '0911222333',
                'ReceiverName' => '測試收件者',
                'ReceiverPhone' => '0226550115',
                'ReceiverCellPhone' => '0933222111',
                'ReceiverEmail' => 'test_emjhdAJr@test.com.tw',
                'TradeDesc' => '測試交易敘述',
                'ServerReplyURL' => $homeUrl,
                'LogisticsC2CReplyURL' =>'',
                'Remark' => '測試備註',
                'PlatformID' => '',
            );

            $this->ecpayLogistics->SendExtend = array(
                'ReceiverStoreID' => '991182',
                'ReturnStoreID' => '991182'
            );
            // BGCreateShippingOrder()
            $Result = $this->ecpayLogistics->BGCreateShippingOrder();
//            $this->logger->info('result: ', $Result);
            echo '<pre>' . print_r($Result, true) . '</pre>';
        } catch(\Exception $e) {
            echo $e->getMessage();
        }
    }
}
