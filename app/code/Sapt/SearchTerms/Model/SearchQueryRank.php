<?php
namespace Sapt\SearchTerms\Model;

class SearchQueryRank extends \Magento\Framework\Model\AbstractModel implements \Sapt\SearchTerms\Api\Data\SearchQueryRankInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'sapt_search_query_rank';

    protected function _construct()
    {
        $this->_init('Sapt\SearchTerms\Model\ResourceModel\SearchQueryRank');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
