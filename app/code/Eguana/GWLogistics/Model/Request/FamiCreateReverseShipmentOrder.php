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
        $merchantId = $this->_helper->getMerchantId($rma->getStoreId());
        $allPayLogisticsId = '';
        $serverReplyURL = $this->_helper->getReverseLogisticsOrderReplyUrl($rma->getStoreId());
        $items = $this->_getItemData($rma);
        $goodsName = (isset($items['goodsName']) && $items['goodsName']) ? $items['goodsName']  : '';
        $goodsAmount = (isset($items['goodsAmount']) && $items['goodsAmount']) ? $items['goodsAmount']  : 0;
        $senderName = $this->_helper->getSenderName($rma->getStoreId());
        $senderPhone = $this->_helper->getSenderPhone($rma->getStoreId());
        $quantity = (isset($items['quantity']) && $items['quantity']) ? $items['quantity']  : '';
        $cost = (isset($items['cost']) && $items['cost']) ? $items['cost']  : '';
        $platformId = $this->_helper->getPlatformId($rma->getStoreId());

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
