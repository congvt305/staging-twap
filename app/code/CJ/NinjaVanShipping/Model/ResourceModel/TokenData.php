<?php

namespace CJ\NinjaVanShipping\Model\ResourceModel;

class TokenData extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ninjavan_access_token', \CJ\NinjaVanShipping\Model\TokenData::TOKEN_ID);
    }

}
