<?php

namespace CJ\VLogicOrder\Model\ResourceModel;

class TokenData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('vlogic_access_token', \CJ\VLogicOrder\Model\TokenData::TOKEN_ID);
    }

}
