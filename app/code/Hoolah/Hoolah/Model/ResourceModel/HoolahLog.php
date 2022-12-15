<?php 
    namespace Hoolah\Hoolah\Model\ResourceModel;
    
    class HoolahLog extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
    {
        public function _construct()
        {
            $this->_init('hoolah_log', null);
        }
    }