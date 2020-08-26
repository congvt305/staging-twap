<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/20/20
 * Time: 5:47 PM
 */

namespace Eguana\GWLogistics\Model\Request;

use Eguana\GWLogistics\Model\Lib\EcpayIsCollection;
use Eguana\GWLogistics\Model\Lib\EcpayLogisticsSubType;
use Eguana\GWLogistics\Model\Lib\EcpayLogisticsType;
use Magento\Sales\Api\Data\OrderInterface;

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
    /**
     * @var \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface
     */
    private $quoteCvsLocationRepository;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository,
        \Psr\Log\LoggerInterface $logger,
        \Eguana\GWLogistics\Model\Lib\EcpayLogistics $ecpayLogistics,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Eguana\GWLogistics\Helper\Data $helper
    ) {
        $this->helper = $helper;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->logger = $logger;
        $this->ecpayLogistics = $ecpayLogistics;
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    public function sendRequest($order) {
        $result = [];
        try {
            $cvsLocation = $this->getCvsLocation($order);
            $this->logger->info('gwlogistics | original cvsLocation ID for create order #' . $order->getEntityId(). '|'. $cvsLocation->getId());
            $dataTime = $this->dateTimeFactory->create();
            $merchantId = $this->helper->getMerchantId($order->getStoreId());
            $platformId = $this->helper->getPlatformId($order->getStoreId()) ?? '';
            $merchantTradeDate = $dataTime->date('Y/m/d H:i:s');
            $merchantTradeNo = ($this->helper->getMode($order->getStoreId()) === '0' || $this->helper->getServerType($order->getStoreId()) === '0') ? 'no'. $dataTime->date('YmdHis') : $order->getIncrementId() ;
            $hashKey = $this->helper->getHashKey($order->getStoreId());
            $hashIv = $this->helper->getHashIv($order->getStoreId());
            $logisticsType = EcpayLogisticsType::CVS;
            $logisticsSubType = $cvsLocation->getLogisticsSubType();

            $items = $this->getItemData($order);
            $goodsAmount = $items['goodsAmount'];
            $goodsName = $items['goodsName'];

            //Characters are limited to 10 characters (upto 5 Chinese characters, 10 English characters)
            $senderName = $this->helper->getSenderName($order->getStoreId()); //no space not more than 10.
            $senderPhone = $this->helper->getSenderPhone($order->getStoreId()); //no space not more than 10.
            $senderCellPhone = $this->helper->getSenderCellPhone($order->getStoreId()); //no space not more than 10.
            //Character limit is 4-10 characters (Chinese2-5 characters, English 4-10 characters)
            $receiverName = $order->getShippingAddress()->getLastname() . $order->getShippingAddress()->getFirstname();
            $receiverName = (strlen($receiverName) > 10) ? substr($receiverName,0,10): $receiverName;

            $receiverPhone = $order->getShippingAddress()->getTelephone();
            $receiverEmail = $order->getShippingAddress()->getEmail();
            $remarks = $order->getDeliveryMessage() ?? '';
            $remarks = (strlen($remarks) > 200) ? substr($remarks,0,200) : $remarks;
            $serverReplyURL = $this->helper->getCreateShipmentReplyUrl();
            $receiverStoreID = $cvsLocation->getCvsStoreId(); //no need, only for C2C
            //for test, sender name, receiver name receiver phone/cellphone , ReceiverStoreID ReturnStoreID are required....!!
            $this->logger->info('gwlogistics | original order->storeid | ' . $order->getStoreId());
            $websitetMerchantId = $this->scopeConfig->getValue(
                'carriers/gwlogistics/merchant_id',
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                $order->getStoreId()
            );
            $storeMerchantId = $this->scopeConfig->getValue(
                'carriers/gwlogistics/merchant_id',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $order->getStoreId()
            );
            $this->logger->info('gwlogistics | original  direct called MerchantId | ' . $websitetMerchantId);
            $this->logger->info('gwlogistics | original  direct called MerchantId | ' . $storeMerchantId);
            $this->logger->info('gwlogistics | original param MerchantId | ' . $merchantId);
            $this->logger->info('gwlogistics | original param MerchantTradeNo | ' . $merchantTradeNo);
            $this->logger->info('gwlogistics | original param MerchantTradeDate | ' . $merchantTradeDate);
            $this->logger->info('gwlogistics | original param LogisticsType | ' . $logisticsType);
            $this->logger->info('gwlogistics | original param LogisticsSubType | ' . $logisticsSubType);
            $this->logger->info('gwlogistics | original param GoodsAmount | ' . $goodsAmount);
            $this->logger->info('gwlogistics | original param CollectionAmount | 0' );
            $this->logger->info('gwlogistics | original param IsCollection | ' . EcpayIsCollection::NO);
            $this->logger->info('gwlogistics | original param GoodsName | ' . $goodsName);
            $this->logger->info('gwlogistics | original param SenderName | ' . $senderName);
            $this->logger->info('gwlogistics | original param SenderPhone | ' . $senderPhone);
            $this->logger->info('gwlogistics | original param SenderCellPhone | ' . $senderCellPhone);
            $this->logger->info('gwlogistics | original param ReceiverName | ' . $receiverName);
            $this->logger->info('gwlogistics | original param ReceiverPhone | ' . $receiverPhone);
            $this->logger->info('gwlogistics | original param ReceiverCellPhone | ' . $receiverPhone);
            $this->logger->info('gwlogistics | original param ReceiverEmail | ' . $receiverEmail);
            $this->logger->info('gwlogistics | original param TradeDesc | '. '');
            $this->logger->info('gwlogistics | original param ServerReplyURL | '. $serverReplyURL);
            $this->logger->info('gwlogistics | original param ClientReplyURL | '. '');
            $this->logger->info('gwlogistics | original param LogisticsC2CReplyURL | '. '');
            $this->logger->info('gwlogistics | original param Remark | '. $remarks);
            $this->logger->info('gwlogistics | original param PlatformID | '. $platformId);
            $params = [
                'MerchantID' => $merchantId,//
                'MerchantTradeNo' => $merchantTradeNo,
                'MerchantTradeDate' => $merchantTradeDate,//
                'LogisticsType' => $logisticsType,//
                'LogisticsSubType' => $logisticsSubType,//
                'GoodsAmount' => $goodsAmount,//
                'CollectionAmount' => 0,
                'IsCollection' => EcpayIsCollection::NO,
                'GoodsName' => $goodsName,
                'SenderName' => $senderName, //
                'SenderPhone' => $senderPhone,
                'SenderCellPhone' => $senderCellPhone,
                'ReceiverName' => $receiverName, //
                'ReceiverPhone' => $receiverPhone, //required for c2c
                'ReceiverCellPhone' => $receiverPhone,
                'ReceiverEmail' => $receiverEmail,
                'TradeDesc' => '',
                'ServerReplyURL' => $serverReplyURL,//
                'ClientReplyURL' => '',
                'LogisticsC2CReplyURL' => '',
                'Remark' => $remarks,
                'PlatformID' => $platformId,
            ];
//        $params = [
//            'MerchantID' => '2000132',
//            'MerchantTradeNo' => 'no' . date('YmdHis'),
//            'MerchantTradeDate' => date('Y/m/d H:i:s'),
//            'LogisticsType' => EcpayLogisticsType::CVS,
//            'LogisticsSubType' => EcpayLogisticsSubType::UNIMART,
//            'GoodsAmount' => 1500,
//            'CollectionAmount' => 0,
//            'IsCollection' => EcpayIsCollection::NO,
//            'GoodsName' => '測試商品',
//            'SenderName' => '測試寄件者',
//            'SenderPhone' => '0226550115',
//            'SenderCellPhone' => '0911222333',
//            'ReceiverName' => '測試收件者',
//            'ReceiverPhone' => '0226550115',
//            'ReceiverCellPhone' => '0933222111',
//            'ReceiverEmail' => 'test_emjhdAJr@test.com.tw',
//            'TradeDesc' => '測試交易敘述',
//            'ServerReplyURL' => $serverReplyURL,
//            'LogisticsC2CReplyURL' => "",
//            'Remark' => '測試備註',
//            'PlatformID' => '',
//        ];
            $this->logger->info('gwlogistics | original params for create order', $params);
            $this->logger->info('gwlogistics | original hashKey for create order', [$hashKey]);
            $this->logger->info('gwlogistics | original hasIv for create order', [$hashIv]);


            $this->ecpayLogistics->HashKey = $hashKey;
            $this->ecpayLogistics->HashIV = $hashIv;

            $this->ecpayLogistics->Send = $params;
            $this->ecpayLogistics->SendExtend = [
                'ReceiverStoreID' => $receiverStoreID, //cvs store id from map request, b2c do not send
                'ReturnStoreID' => '' //cvs store id from map request, b2c do not send
            ];
//            $result = $this->ecpayLogistics->BGCreateShippingOrder();
//            if (isset($result['CheckMacValue'])) {
//                if (!$this->helper->validateCheckMackValue($result, $order->getStoreId())) {
//                    throw new \Exception(__('CheckMacValue is not valid'));
//                }
//            } //todo uncomment later
        } catch (\Exception $e) {
            $this->logger->critical('GWL create shipping order failed for internal valication');
            $this->logger->critical($e->getMessage());
            throw $e;
        }
        return $result;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    private function getCvsLocation($order)
    {
        $cvsLocationId = $order->getShippingAddress()->getCvsLocationId();
        return $this->quoteCvsLocationRepository->getById($cvsLocationId);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    private function getItemData($order)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $orderItems = $order->getAllItems();
        $firstItem = reset($orderItems);

        $count = $order->getTotalItemCount();
        //'/[\^\'`\!@#%&\*\+\\\"<>\|_\[\]]+/'
        $goodsName = str_replace(['^', '`', '\'', '!', '@','#','%', '&', '\\', '"', '<', '>', '|', '_', '[', ']',   '+', '*'], '', $firstItem->getName());
        $goodsName = (strlen($goodsName) > 30) ? substr($goodsName,0,30).'...': $goodsName;
        $goodsName = $count > 1 ? $goodsName . __(' and others.'): $goodsName;

        return [
            'goodsAmount' => intval($order->getSubtotal()),
            'goodsName' => $goodsName,
            'quantity' => $order->getTotalItemCount(),
            'cost' => intval($order->getGrandTotal()),
        ];
    }

}
