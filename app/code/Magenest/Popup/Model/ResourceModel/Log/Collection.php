<?php
namespace Magenest\Popup\Model\ResourceModel\Log;

use Magenest\Popup\Model\ResourceModel\Log;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /** @var string */
    protected $_idFieldName = 'log_id';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Magenest\Popup\Model\Log::class, Log::class);
    }
}
