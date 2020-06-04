<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/3/20
 * Time: 1:06 PM
 */

namespace Eguana\Directory\Model\ResourceModel\City;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Eguana\Directory\Model\City::class, \Eguana\Directory\Model\ResourceModel\City::class);
        $this->addOrder('default_name', \Magento\Framework\Data\Collection::SORT_ORDER_ASC);
    }
}
