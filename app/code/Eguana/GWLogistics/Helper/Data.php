<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/20/20
 * Time: 7:51 PM
 */

namespace Eguana\GWLogistics\Helper;


use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    /**
     * @var \Eguana\GWLogistics\Model\Lib\EcpayCheckMacValue
     */
    private $ecpayCheckMacValue;

    public function __construct(
        \Eguana\GWLogistics\Model\Lib\EcpayCheckMacValue $ecpayCheckMacValue,
        Context $context
    ) {
        parent::__construct($context);
        $this->ecpayCheckMacValue = $ecpayCheckMacValue;
    }

    public function getCarrierTitle() {
        return $this->scopeConfig->getValue(
            'carriers/gwlogistics/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE

        );
    }
    public function getMapServerReplyUrl() {
       return $this->_getUrl('eguana_gwlogistics/SelectedCvsNotify', ['_secure' => true]);
    }

    public function getCreateShipmentReplyUrl() {
        return $this->_getUrl('eguana_gwlogistics/OrderStatusNotify', ['_secure' => true]);
    }

    public function getReverseLogisticsOrderReplyUrl() {
        return $this->_getUrl('eguana_gwlogistics/ReverseOrderStatusNotify', ['_secure' => true]);
    }

    public function validateCheckMackValue(array $params): bool
    {
        //todo: config value
        $hashKey = '';
        $hasIv = '';
        $checkMackValue = $params['CheckMacValue'];
        $checkMackValueFound = $this->ecpayCheckMacValue->Generate($params, $hashKey, $hasIv);
        return $checkMackValue === $checkMackValueFound;

    }

}
