<?php
namespace Sapt\SearchTerms\Model\ResourceModel;

class SearchQueryRank extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('sapt_search_query_rank','entity_id');
    }
}
