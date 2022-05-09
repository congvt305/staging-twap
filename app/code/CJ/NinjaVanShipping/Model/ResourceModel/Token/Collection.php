<?php
namespace CJ\NinjaVanShipping\Model\ResourceModel\Token;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\CJ\NinjaVanShipping\Model\TokenData::class, \CJ\NinjaVanShipping\Model\ResourceModel\TokenData::class);
        $this->setMainTable('ninjavan_access_token');
    }

}
