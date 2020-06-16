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

    public function loadByCode(\Eguana\Directory\Model\City $city, $cityCode, $regionId)
    {
        return $this->loadByRegion($city, $regionId, (string)$cityCode, 'code');
    }

    public function loadByRegion($object, $regionId, $value, $field)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['city' => $this->getMainTable()]
        )->where(
            'city.region_id = ?',
            $regionId
        )->where(
            "city.{$field} = ?",
            $value
        );

        $data = $connection->fetchRow($select);
        if ($data) {
            $object->setData($data);
        }

        $this->_afterLoad($object);

        return $this;
    }
}
