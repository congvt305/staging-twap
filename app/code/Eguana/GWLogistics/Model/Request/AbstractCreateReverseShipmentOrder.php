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

    /**
     * @param \Magento\Rma\Model\Rma $rma
     * @return array
     */
    public function sendRequest($rma)
    {
        $hashKey = $this->_helper->getHashKey($rma->getStoreId());
        $hashIv = $this->_helper->getHashIv($rma->getStoreId());
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
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() === 'simple') {
                $orderItemArr[] = $orderItem;
                $quantity += (int)$orderItem->getQtyOrdered();
            }
        }
        $count = count($orderItemArr);
        $item = reset($orderItemArr);

        $goodsName = str_replace(['^', '`', '\'', '!', '@','#','%', '&', '\\', '"', '<', '>', '|', '_', '[', ']',   '+', '*'], '', $item->getName());
        $goodsName = (strlen($goodsName) > 30) ? substr($goodsName,0,30).'...': $goodsName;
        $goodsName = $count > 1 ? $goodsName . __(' and others.'): $goodsName;

        $quantity = (string)$quantity;

        return [
            'goodsAmount' => (int)round($order->getBaseGrandTotal(), 0),
            'goodsName' => $goodsName,
            'quantity' => $quantity,
            'cost' => (int)round($order->getBaseGrandTotal(), 0),
        ];
    }

}
