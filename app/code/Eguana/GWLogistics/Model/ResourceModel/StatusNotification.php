<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/28/20
 * Time: 7:49 AM
 */

namespace Eguana\GWLogistics\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class StatusNotification extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eguana_gwlogistics_logistics_status_notification', 'entity_id');
    }
}
