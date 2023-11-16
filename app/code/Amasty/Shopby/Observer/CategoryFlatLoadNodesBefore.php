<?php

namespace Amasty\Shopby\Observer;

use Magento\Framework\Event\ObserverInterface;

class CategoryFlatLoadNodesBefore implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Zend_Db_Select_Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var \Zend_Db_Select $select
         */
        $select = $observer->getEvent()->getSelect();
        $select->columns('main_table.thumbnail');
    }
}
