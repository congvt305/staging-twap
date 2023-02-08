<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Special Occasion Coupons for Magento 2
*/
namespace Amasty\Birth\Model\Sender;

class Birth extends AbstractSender
{
    public function execute()
    {
        $days = intVal($this->helper->getModuleConfig('birth/days'));
        if ($days > 0) // after birthday
            $time = strtotime("-$days days");
        else {
            $days = abs($days);
            $time = strtotime("+$days days");
        }

        $collection = $this->_getCollection()
            ->joinAttribute('dob','customer/dob', 'entity_id');
        $select =  $collection->getSelect();
        $select->where(new \Zend_Db_Expr(
                "DATE_FORMAT(`at_dob`.`dob`, '%m-%d') = '".date('m-d', $time)."'"
            )
        );

        foreach ($collection as $customer){
            $this->_emailToCustomer($customer, 'birth');
        }
    }
}
