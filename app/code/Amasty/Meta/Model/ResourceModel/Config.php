<?php
namespace Amasty\Meta\Model\ResourceModel;

class Config extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    public function _construct()
    {
        $this->_init('amasty_meta_config', 'config_id');
    }

}
