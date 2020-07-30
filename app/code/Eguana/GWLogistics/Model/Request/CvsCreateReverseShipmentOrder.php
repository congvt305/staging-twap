<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/15/20
 * Time: 4:45 AM
 */

namespace Eguana\GWLogistics\Model\Request;

use Magento\Sales\Api\Data\OrderInterface;

class CvsCreateReverseShipmentOrder
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    /**
     * @var \Eguana\GWLogistics\Model\Lib\EcpayLogistics
     */
    protected $_ecpayLogistics;
    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Psr\Log\LoggerInterface $logger,
        \Eguana\GWLogistics\Model\Lib\EcpayLogistics $ecpayLogistics,
        \Eguana\GWLogistics\Helper\Data $helper
    ) {
        $this->_logger = $logger;
        $this->_ecpayLogistics = $ecpayLogistics;
        $this->_helper = $helper;
        $this->_orderRepository = $orderRepository;
    }

    public function sendRequest($rma)
    {
        $logisticsSubType = $rma->getData('shipping_preference');
        $hashKey = $this->_helper->getHashKey();
        $hashIv = $this->_helper->getHashIv();
        try {
            $this->_ecpayLogistics->HashKey = $hashKey;
            $this->_ecpayLogistics->HashIV = $hashIv;
            $this->_ecpayLogistics->Send = $this->_getParams($rma);
            $result = $logisticsSubType === 'UNIMART' ? $this->_ecpayLogistics->CreateUnimartB2CReturnOrder()
                : $this->_ecpayLogistics->CreateFamilyB2CReturnOrder();
            $this->_logger->debug('GWL create reverse logistic order result: ', $result);
            return $result; //RtnMerchantTradeNo | RtnOrderNo or |ErrorMessage result array
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }
    }

    protected function _getParams($rma)
    {
        return [];
    }

    /**
     * @param \Magento\Rma\Api\Data\RmaInterface $rma
     */
    protected function _getItemData($rma)
    {
        /** @var OrderInterface $order */
        $order = $this->_orderRepository->get($rma->getOrderId());
        $orderItems = $order->getItems();
        $orderItemArr = [];
        $quantity = '';
        $cost = '';
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() === 'simple') {
                $orderItemArr[] = $orderItem;
                $quantity .= '#' . (string)(int)$orderItem->getQtyOrdered();
                $cost .= '#' . (string)(int)round($orderItem->getPrice(), 0);
            }
        }
        $count = count($orderItemArr);
        $item = reset($orderItemArr);

        $itemName = $item->getName();
        $itemName = (strlen($itemName) > 30) ? substr($itemName,0,30).'...': $itemName;
        $itemName = $count > 1 ? $itemName . __(' and others.'): $itemName;

        $quantity = substr($quantity,0,1);
        $quantity = (strlen($quantity) > 50) ? substr($quantity,0,50) : $quantity;

        $cost = substr($cost,0,1);
        $cost = (strlen($cost) > 50) ? substr($cost,0,50) : $cost;

        return [
            'goodsAmount' => (int)round($order->getSubtotal(), 0),
            'goodsName' => $itemName,
            'quantity' => $quantity,
            'cost' => $cost,
        ];
    }

}
