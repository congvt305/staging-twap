<?php
    namespace Hoolah\Hoolah\Model\ResourceModel\HoolahLog;
    
    class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
    {
        public function _construct(){
            $this->_init('Hoolah\Hoolah\Model\HoolahLog', 'Hoolah\Hoolah\Model\ResourceModel\HoolahLog');
        }
    }
 ?>