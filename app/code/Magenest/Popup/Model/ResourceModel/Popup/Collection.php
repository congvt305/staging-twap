<?php
namespace Magenest\Popup\Model\ResourceModel\Popup;

use Magenest\Popup\Model\ResourceModel\Popup;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /** @var string */
    protected $_idFieldName = 'popup_id';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Magenest\Popup\Model\Popup::class, Popup::class);
    }

    /**
     * Reset collection
     *
     * @return Collection
     */
    public function reset()
    {
        $this->_totalRecords = null;
        return $this->_reset();
    }
}
