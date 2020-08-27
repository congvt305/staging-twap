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
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return float|int|null
     */
    protected function getItemCount($order) {
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
