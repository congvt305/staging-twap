<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Special Occasion Coupons for Magento 2
*/

namespace Amasty\Birth\Model\Sender;

class Reg extends AbstractSender
{
    public function execute()
    {
        $days = (int)$this->helper->getModuleConfig('reg/days');
        if ($days < 0) {
            return;
        }

        $collection = $this->_getCollection();
        $select = $collection->getSelect();
        $select->where(
            new \Zend_Db_Expr(
                "DATE_FORMAT(created_at, '%Y-%m-%d') = '" . $this->date->date('Y-m-d', "-$days days") . "'"
            )
        );

        foreach ($collection->getItems() as $customer) {
            $this->_emailToCustomer($customer, 'reg');
        }
    }
}
