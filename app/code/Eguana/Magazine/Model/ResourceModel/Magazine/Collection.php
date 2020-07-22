<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/16/20
 * Time: 12:58 AM
 */
namespace Eguana\Magazine\Model\ResourceModel\Magazine;

use Eguana\Magazine\Api\Data\MagazineInterface;
use Eguana\Magazine\Model\Magazine;
use Eguana\Magazine\Model\ResourceModel\AbstractCollection;
use Eguana\Magazine\Model\ResourceModel\Magazine as MagazineAlias;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Store\Model\Store as StoreAlias;

/**
 * Collection class to retrieve the data from db table
 * Class Collection
 */
class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Event prefix
     * @var string
     */
    protected $eventPrefix = 'eguana_magazine_collection';

    /**
     * Event object
     * @var string
     */
    protected $eventObject = 'magazine_collection';

    /**
     * @var
     */
    protected $aggregations;

    /**
     * Define resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Magazine::class, MagazineAlias::class);
        $this->_map['fields']['store'] = 'store_table.store_id';
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }

    /**
     * After load method
     * @return Collection
     */
    protected function _afterLoad()
    {
        try {
            $entityMetadata = $this->metadataPool->getMetadata(MagazineInterface::class);
            $this->performAfterLoad('eguana_magazine_store', $entityMetadata->getLinkField());
            return parent::_afterLoad();
        } catch (\Exception $exception) {
            $this->_logger->debug($exception->getMessage());
        }
    }

    /**
     * Get aggregations
     * @return mixed
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * Set aggregations
     * @param $aggregations
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Retrieve all ids for collection
     * Backward compatibility with EAV collection
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol(
            $this->_getAllIdsSelect($limit, $offset),
            $this->_bindParams
        );
    }

    /**
     * Get search criteria
     * @return |null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set Search criteria
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     *
     * @return $this|SearchResultInterface
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set item list
     * @param array|null $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Options method
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('entity_id', 'title');
    }

    /**
     * Add filter by store
     * @param array|int|StoreAlias $store
     * @param bool $withAdmin
     * @return $this|Collection
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
            $this->setFlag('store_filter_added', true);
        }
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $entityMetadata = $this->metadataPool->getMetadata(MagazineInterface::class);
        $this->joinStoreRelationTable('eguana_magazine_store', $entityMetadata->getLinkField());
    }
}
