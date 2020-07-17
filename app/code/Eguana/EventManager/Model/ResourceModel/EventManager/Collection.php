<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/6/20
 * Time: 4:10 PM
 */
namespace Eguana\EventManager\Model\ResourceModel\EventManager;

use Eguana\EventManager\Model\ResourceModel\AbstractCollection;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Eguana\EventManager\Api\Data\EventManagerInterface;
use Eguana\EventManager\Model\EventManager as EventManagerModel;
use Eguana\EventManager\Model\ResourceModel\EventManager as EventManagerResourceModel;
use Magento\Framework\Api\Search\AggregationInterface;

/**
 * Collection class to retrieve the data from db table
 * Class Collection
 */
class Collection extends AbstractCollection implements SearchResultInterface
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Event prefix
     * @var string
     */
    private $eventPrefix = 'events_manage_grid_collection';

    /**
     * Event object
     * @var string
     */
    private $eventObject = 'events_manage_grid_collection';

    /**
     * @var
     */
    private $aggregations;

    /**
     * Initialization of Model and ResourceModel
     */
    public function _construct()
    {
        $this->_init(
            EventManagerModel::class,
            EventManagerResourceModel::class
        );
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
            $entityMetadata = $this->metadataPool->getMetadata(EventManagerInterface::class);
            $this->performAfterLoad('eguana_event_manager_store', $entityMetadata->getLinkField());
            return parent::_afterLoad();
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * Get aggregations
     * @return AggregationInterface
     */

    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * Set aggregations
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Retrieve all ids for collection
     * Backward compatibility with EAV collection
     * @param int $limit
     * @param int $offset
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
     * Get search criteria.
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return SearchResultInterface
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
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
     * @return $this|SearchResultInterface
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param array|null $items
     * @return $this|SearchResultInterface
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
        return $this->_toOptionArray('entity_id', 'event_title');
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
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
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        try {
            $entityMetadata = $this->metadataPool->getMetadata(EventManagerInterface::class);
            $this->joinStoreRelationTable('eguana_event_manager_store', $entityMetadata->getLinkField());
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }
}
