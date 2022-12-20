<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Special Occasion Coupons for Magento 2
*/
namespace Amasty\Birth\Model\Sender;

class Activity extends AbstractSender
{
    public function execute()
    {
        $days = intVal($this->helper->getModuleConfig('activity/days'));
        if ($days < 0) {
            return;
        }

        $db = $this->resource->getConnection('core_read');
        $select = $db->select()
            ->from($this->resource->getTableName('customer_log'), array('customer_id'))
            ->having('MAX(last_login_at) < "'. $this->date->date('Y-m-d', strtotime("-$days days")) .'"')
            ->group('customer_id')
            ->limit(1000);

        $ids = $db->fetchCol($select);
        if (!$ids) {
            return;
        }

        $collection = $this->_getCollection()
            ->addFieldToFilter('entity_id', array('in'=>$ids));
        $collection->getSelect()->order('entity_id DESC');
        $collection->load();

        foreach ($collection as $customer){
            $this->_emailToCustomer($customer, 'activity');
        }
    }
}
