<?php

namespace Amasty\CPS\Model\ResourceModel\Product;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\DB\Select;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * @inheritdoc
     */
    public function getAllIds($limit = null, $offset = null)
    {
        $this->_renderFilters();
        $this->_renderOrders();
        $idsSelect = $this->_getClearSelect();

        /**
         * Keep "order" part in getAllIds() method for merchandising search.
         * Using in Amasty/VisualMerch/Block/Adminhtml/Products/Listing::search()
         */
        $idsSelect->setPart(Select::ORDER, $this->getSelect()->getPart(Select::ORDER));
        $idsSelect->columns('e.' . $this->getEntity()->getIdFieldName());
        $idsSelect->limit($limit, $offset);

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }
}
