<?php
namespace Satp\SearchTerms\Model;

class SearchQueryRank extends \Magento\Framework\Model\AbstractModel implements \Satp\SearchTerms\Api\Data\SearchQueryRankInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'satp_search_query_rank';

    protected function _construct()
    {
        $this->_init('Satp\SearchTerms\Model\ResourceModel\SearchQueryRank');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
