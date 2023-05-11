<?php
    namespace Hoolah\Hoolah\Model;
    
    class HoolahLog extends \Magento\Framework\Model\AbstractModel
    {
        public function _construct()
        {
            $this->_init('Hoolah\Hoolah\Model\ResourceModel\HoolahLog');
        }
    }