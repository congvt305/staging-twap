<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/15/20
 * Time: 4:45 AM
 */

namespace Eguana\GWLogistics\Model\Request;

use Magento\Sales\Api\Data\OrderInterface;

class AbstractCreateReverseShipmentOrder
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
            $result = $this->_getResult();
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
            $result = ['ErrorMessage' => $e->getMessage()];
        }
        return $result; //RtnMerchantTradeNo | RtnOrderNo or |ErrorMessage result array
    }

    protected function _getParams($rma)
    {
        return [];
    }

    protected function _getResult()
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
        $goodsName = '';
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() === 'simple') {
                $orderItemArr[] = $orderItem;
                $goodsName .= '#' . $orderItem->getName();
                $quantity .= '#' . (string)(int)$orderItem->getQtyOrdered();
                $cost .= '#' . (string)(int)round($orderItem->getPrice(), 0);
            }
        }
        $count = count($orderItemArr);
        $item = reset($orderItemArr);

        $itemName = $item->getName();
        $itemName = (strlen($itemName) > 30) ? substr($itemName,0,30).'...': $itemName;
        $itemName = $count > 1 ? $itemName . __(' and others.'): $itemName;

        $quantity = substr($quantity,1);
        $goodsName = substr($goodsName,1);
        $cost = substr($cost,1);

        //when $quantity is longer than 50 then make cost and quantity  one string
        $goodsName = (strlen($quantity) > 50) ? $itemName : $goodsName;
        $cost = (strlen($quantity) > 50) ? (string)(int)round($order->getSubtotal(), 0) : $cost;
        $quantity = (strlen($quantity) > 50) ? '1' : $quantity;

        //when $goodsName is longer than 50 then make cost and quantity  one string
        $quantity = (strlen($goodsName) > 50) ? '1' : $quantity;
        $cost = (strlen($goodsName) > 50) ? (string)(int)round($order->getSubtotal(), 0) : $cost;
        $goodsName = (strlen($goodsName) > 50) ? $itemName : $goodsName;

        //when $cost is longer than 50 then make cost and quantity  one string
        $quantity = (strlen($cost) > 50) ? '1' : $quantity;
        $cost = (strlen($cost) > 50) ? (string)(int)round($order->getSubtotal(), 0) : $cost;
        $goodsName = (strlen($cost) > 50) ? $itemName : $goodsName;

        return [
            'goodsAmount' => (int)round($order->getSubtotal(), 0),
            'goodsName' => $goodsName,
            'quantity' => $quantity,
            'cost' => $cost,
        ];
    }

}
