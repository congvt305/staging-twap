<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Special Occasion Coupons for Magento 2
*/

namespace Amasty\Birth\Model\Sender;

class Wishlist extends AbstractSender
{
    public function execute()
    {
        $days = (int)$this->helper->getModuleConfig('wishlist/days');
        if ($days < 0) {
            return;
        }

        $db = $this->resource->getConnection('core_read');

        $select = $db->select()
            ->from(
                [
                    'w' => $this->resource->getTableName('wishlist')
                ],
                ['customer_id']
            )
            ->joinInner(
                [
                    'i' => $this->resource->getTableName('wishlist_item')
                ],
                'w.wishlist_id = i.wishlist_id',
                []
            )
            ->having(new \Zend_Db_Expr(
                "COUNT(i.product_id)>0 AND DATE_FORMAT(MAX(updated_at), '%y-%m-%d') <= '"
                 . $this->date->date('y-m-d', strtotime("-$days days"))
                . "'"
            ))
            ->group('customer_id')
            ->limit(1000);

        $ids = $db->fetchCol($select);
        if (!$ids) {
            return;
        }

        $collection = $this->_getCollection()
            ->addFieldToFilter('entity_id', ['in' => $ids]);
        $collection->getSelect()->order('entity_id DESC');
        $collection->load();

        foreach ($collection as $customer) {
            $this->_emailToCustomer($customer, 'wishlist');
        }
    }
}
