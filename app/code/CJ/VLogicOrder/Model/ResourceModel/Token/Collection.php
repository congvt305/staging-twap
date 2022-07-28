<?php
namespace CJ\VLogicOrder\Model\ResourceModel\Token;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\CJ\VLogicOrder\Model\TokenData::class, \CJ\VLogicOrder\Model\ResourceModel\TokenData::class);
        $this->setMainTable('vlogic_access_token');
    }

}
