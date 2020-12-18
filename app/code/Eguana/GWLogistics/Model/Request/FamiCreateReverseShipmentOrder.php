<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/15/20
 * Time: 4:45 AM
 */

namespace Eguana\GWLogistics\Model\Request;

class FamiCreateReverseShipmentOrder extends \Eguana\GWLogistics\Model\Request\AbstractCreateReverseShipmentOrder
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

        $goodsName = $this->_helper->getGoodsNamePrefix($order->getStoreId()) . ' Item X ' . (string)$this->getItemCount($order) ;
        $goodsAmount = intval($order->getSubtotal()); //todo this meets only full return, need to fix when partial refund

//        $senderName = $this->_helper->getSenderName($order->getStoreId()); //Characters are limited to 10 characters (upto 5 Chinese characters, 10 English characters)
        $senderName = $order->getCustomerLastname() . $order->getCustomerFirstname(); //Characters are limited to 10 characters (upto 5 Chinese characters, 10 English characters)
        $senderPhone = $rma->getData('customer_custom_phone') ?? $order->getShippingAddress()->getTelephone();
        $platformId = $this->_helper->getPlatformId($order->getStoreId());

        $quantity = (string)$order->getTotalItemCount(); //todo this meets only full return, need to fix when partial refund
        $cost = (string)intval($order->getSubtotal()); //todo this meets only full return, need to fix when partial refund

        return [
            'MerchantID' => $merchantId,
            'AllPayLogisticsID' => $allPayLogisticsId,
            'ServerReplyURL' => $serverReplyURL,
            'GoodsName' => $goodsName,
            'GoodsAmount' => $goodsAmount,
            'SenderName' => $senderName,
            'SenderPhone' => $senderPhone,
            'Remark' => '',
            'Quantity' => $quantity,
            'Cost' => $cost,
            'PlatformID' => $platformId,
        ];
    }

    protected function _getResult()
    {
        return $this->_ecpayLogistics->CreateFamilyB2CReturnOrder();
    }
}
