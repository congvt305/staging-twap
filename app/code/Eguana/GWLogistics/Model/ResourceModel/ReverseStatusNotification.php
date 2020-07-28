<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/28/20
 * Time: 7:50 AM
 */

namespace Eguana\GWLogistics\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ReverseStatusNotification extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
       $this->_init('eguana_gwlogistics_reverse_logistics_status_notification', 'entity_id');
    }
}
