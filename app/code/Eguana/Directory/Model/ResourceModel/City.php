<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/3/20
 * Time: 1:04 PM
 */

namespace Eguana\Directory\Model\ResourceModel;


class City extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('eguana_directory_region_city', 'city_id');
    }
}
