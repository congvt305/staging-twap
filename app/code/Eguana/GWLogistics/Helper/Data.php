<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/20/20
 * Time: 7:51 PM
 */

namespace Eguana\GWLogistics\Helper;


use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    public function getMapServerReplyUrl() {
       return $this->_getUrl('eguana_gwlogistics/ReceiverServerReply', ['_secure' => true]);
    }

    public function getCreateShipmentReplyUrl() {
        return $this->_getUrl('eguana_gwlogistics/StatusNotify', ['_secure' => true]);
    }

}
