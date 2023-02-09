<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Special Occasion Coupons for Magento 2
*/
namespace Amasty\Birth\Model\Sender;

class Regbirth extends AbstractSender
{
    public function execute()
    {
        $collection = $this->_getCollection();
        $select =  $collection->getSelect();
        $select->where(new \Zend_Db_Expr(
            "DATE_FORMAT(`created_at`, '%m-%d') = '" . $this->date->date('m-d') . "'"
        ))->where(new \Zend_Db_Expr(
                "DATE_FORMAT(`created_at`, '%y-%m-%d') < '" . $this->date->date('y-m-d') . "'"
            )
        );

        foreach ($collection as $customer){
            $this->_emailToCustomer($customer, 'regbirth');
        }
    }
}
