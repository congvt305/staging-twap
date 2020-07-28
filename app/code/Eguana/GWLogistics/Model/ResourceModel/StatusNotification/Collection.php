<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/28/20
 * Time: 7:57 AM
 */

namespace Eguana\GWLogistics\Model\ResourceModel\StatusNotification;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
       $this->_init(\Eguana\GWLogistics\Model\StatusNotification::class, \Eguana\GWLogistics\Model\ResourceModel\StatusNotification::class);
    }

}
