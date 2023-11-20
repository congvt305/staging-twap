<?php
namespace Sapt\GeoTarget\Model;

class GeoTarget extends \Magento\Framework\Model\AbstractModel implements \Sapt\GeoTarget\Api\Data\GeoTargetInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'stap_geotarget';

    protected function _construct()
    {
        $this->_init('Sapt\GeoTarget\Model\ResourceModel\GeoTarget');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
