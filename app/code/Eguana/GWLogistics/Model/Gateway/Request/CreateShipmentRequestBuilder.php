<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/10/20
 * Time: 9:36 AM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Gateway\Request;

use Eguana\GWLogistics\Model\Lib\EcpayLogisticsType;
use Magento\Sales\Model\Order;
use Eguana\GWLogistics\Model\Lib\EcpayIsCollection;
use Magento\Sales\Model\OrderRepository;

class CreateShipmentRequestBuilder implements \Eguana\GWLogistics\Model\Gateway\Request\RequestBuilderInterface
{
    /**
     * @var \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface
     */
    private $quoteCvsLocationRepository;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    private $dateTimeFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    private $helper;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @param \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Eguana\GWLogistics\Helper\Data $helper
     * @param OrderRepository $orderRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Eguana\GWLogistics\Helper\Data $helper,
        OrderRepository $orderRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Prepare data to send GW logistic
     *
     * @param array $buildSubject
     * @return array|null
     */
    public function build(array $buildSubject): ?array
    {
        $order = $buildSubject['order'];
        $data = [];
        try {
            $cvsLocation = $this->getCvsLocation($order);
            $dataTime = $this->dateTimeFactory->create();
            $merchantId = $this->helper->getMerchantId($order->getStoreId());
            $platformId = $this->helper->getPlatformId($order->getStoreId()) ?? '';
            $merchantTradeDate = $dataTime->date('Y/m/d H:i:s');
            $merchantTradeNo = ($this->helper->getMode($order->getStoreId()) === '0' || $this->helper->getServerType($order->getStoreId()) === '0') ? 'no' . $dataTime->date('YmdHis') : $order->getIncrementId();
            $hashKey = $this->helper->getHashKey($order->getStoreId());
            $hashIv = $this->helper->getHashIv($order->getStoreId());
            $logisticsType = EcpayLogisticsType::CVS;
            $logisticsSubType = $cvsLocation->getLogisticsSubType();

            $goodsAmount = intval($order->getSubtotal());
            $goodsName = $this->helper->getGoodsNamePrefix($order->getStoreId()) . ' Item X ' . (string)$this->getItemCount($order);

            //Characters are limited to 10 characters (upto 5 Chinese characters, 10 English characters)
            $senderName = $this->helper->getSenderName($order->getStoreId()); //no space not more than 10.
            $senderPhone = $this->helper->getSenderPhone($order->getStoreId()); //no space not more than 10.
            $senderCellPhone = $this->helper->getSenderCellPhone($order->getStoreId()); //no space not more than 10.
            //Character limit is 4-10 characters (Chinese2-5 characters, English 4-10 characters)
            $receiverName = $order->getShippingAddress()->getLastname() . $order->getShippingAddress()->getFirstname();
            $receiverName = str_replace(' ', '', $receiverName);
            $receiverPhone = $order->getShippingAddress()->getTelephone();
            $receiverEmail = $order->getShippingAddress()->getEmail();
            $remarks = $order->getDeliveryMessage() ?? '';
            $remarks = (strlen($remarks) > 200) ? substr($remarks, 0, 200) : $remarks;
            $serverReplyURL = $this->helper->getCreateShipmentReplyUrl($order->getStoreId());
            $receiverStoreID = $cvsLocation->getCvsStoreId(); //no need, only for C2C
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
            $data = [
                'HashKey' => $hashKey,
                'HashIV' => $hashIv,
                'Params' => $params,
                'ReceiverStoreID' => $receiverStoreID
            ];

            return $data;
        } catch (\Exception $e) {
            $this->logger->critical('GWL create shipping order failed for internal validation');
            $this->logger->critical($e->getMessage());
//            throw $e;
            return $data;
        }
    }

    /**
     * Get cvs location data and re-save if can not get
     *
     * @param  Order $order
     * @return \Eguana\GWLogistics\Api\Data\QuoteCvsLocationInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCvsLocation($order)
    {
        try {
            $cvsLocationId = (int) $order->getShippingAddress()->getCvsLocationId();
            $cvsData = $this->quoteCvsLocationRepository->getById($cvsLocationId);
        } catch (\Exception $e) { //case order doesn't have cvs location id
            $this->logger->critical("Order " . $order->getIncrementId() . ' is missed cvs location id');
            $cvsData = $this->quoteCvsLocationRepository->getByQuoteId($order->getQuoteId());
            $order->getShippingAddress()->setCvsLocationId($cvsData->getLocationId());
            $this->orderRepository->save($order);
            $this->logger->critical("Update order " . $order->getIncrementId() . ' with cvs location id = ' . $cvsData->getLocationId() . ' successfully');
        }
        return $cvsData;
    }

    /**
     * Count total item
     *
     * @param $order
     * @return float|int|null
     */
    private function getItemCount($order) {
        /** @var \Magento\Sales\Api\Data\OrderItemInterface[] $items */
        $items = $order->getItems();
        $totalQty = 0;

        /** @var \Magento\Sales\Api\Data\OrderItemInterface $item */
        foreach ($items as $item) {
            if($item->getProductType() === 'simple') {
                $totalQty += $item->getQtyOrdered();
            }
        }

        return $totalQty;
    }
}
