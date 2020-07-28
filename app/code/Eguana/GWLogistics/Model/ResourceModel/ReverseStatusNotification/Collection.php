<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/28/20
 * Time: 7:57 AM
 */

namespace Eguana\GWLogistics\Model\ResourceModel\ReverseStatusNotification;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
       $this->_init(\Eguana\GWLogistics\Model\ReverseStatusNotification::class, \Eguana\GWLogistics\Model\ResourceModel\ReverseStatusNotification::class);
    }

}
