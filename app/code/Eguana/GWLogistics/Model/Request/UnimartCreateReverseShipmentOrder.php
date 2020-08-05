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
        $merchantId = $this->_helper->getMerchantId($rma->getStoreId());
        $allPayLogisticsId = '';
        $serverReplyURL = $this->_helper->getReverseLogisticsOrderReplyUrl();
        $items = $this->_getItemData($rma);
        $goodsName = (isset($items['goodsName']) && $items['goodsName']) ? $items['goodsName']  : '';
        $goodsAmount = (isset($items['goodsAmount']) && $items['goodsAmount']) ? $items['goodsAmount']  : 0;
        $senderName = $this->_helper->getSenderName($rma->getStoreId()); //Characters are limited to 10 characters (upto 5 Chinese characters, 10 English characters)
        $senderPhone = $this->_helper->getSenderPhone($rma->getStoreId());
        $platformId = $this->_helper->getPlatformId($rma->getStoreId());

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
