<?php
namespace Satp\GeoTarget\Model;

class GeoTarget extends \Magento\Framework\Model\AbstractModel implements \Satp\GeoTarget\Api\Data\GeoTargetInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'stap_geotarget';

    protected function _construct()
    {
        $this->_init('Satp\GeoTarget\Model\ResourceModel\GeoTarget');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
