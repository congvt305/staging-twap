<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/15/20
 * Time: 4:45 AM
 */

namespace Eguana\GWLogistics\Model\Request;

class UnimartCreateReverseShipmentOrder extends \Eguana\GWLogistics\Model\Request\AbstractCreateReverseShipmentOrder
{
    /**
     * @param \Magento\Rma\Api\Data\RmaInterface $rma
     * @return array
     */
    protected function _getParams($rma)
    {
        $order = $this->_orderRepository->get($rma->getOrderId());
        $merchantId = $this->_helper->getMerchantId($order->getStoreId());
//        $allPayLogisticsId = $rma->getIncrementId();
        $allPayLogisticsId = '';
        $serverReplyURL = $this->_helper->getReverseLogisticsOrderReplyUrl($order->getStoreId());

        $goodsName = $this->_helper->getGoodsNamePrefix($order->getStoreId()) . ' Item X ' . (string)$this->getItemCount($order);
        $goodsAmount = intval($order->getSubtotal()); //todo this meets only full return, need to fix when partial refund

        $senderName = $order->getCustomerLastname() . $order->getCustomerFirstname(); //Characters are limited to 10 characters (upto 5 Chinese characters, 10 English characters)
        $senderPhone = $rma->getData('customer_custom_phone') ?? $order->getShippingAddress()->getTelephone();
        $platformId = $this->_helper->getPlatformId($order->getStoreId());

        return [
            'MerchantID' => $merchantId,
            'AllPayLogisticsID' => $allPayLogisticsId,
            'ServerReplyURL' => $serverReplyURL,
            'GoodsName' => $goodsName,
            'GoodsAmount' => $goodsAmount,
            'CollectionAmount' => 0,
            'ServiceType' => '4',
            'SenderName' => $senderName,
            'SenderPhone' => $senderPhone,
            'Remark' => '', //todo: need to test if can be empty
            'PlatformID' => $platformId,
        ];
    }

    protected function _getResult()
    {
        return $this->_ecpayLogistics->CreateUnimartB2CReturnOrder();
    }
}
