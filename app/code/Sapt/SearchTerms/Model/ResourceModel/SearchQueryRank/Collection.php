<?php
namespace Sapt\SearchTerms\Model\ResourceModel\SearchQueryRank;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init('Sapt\SearchTerms\Model\SearchQueryRank','Sapt\SearchTerms\Model\ResourceModel\SearchQueryRank');
    }
}
