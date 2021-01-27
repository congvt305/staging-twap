<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/10/20
 * Time: 4:44 PM
 */
namespace Eguana\Redemption\Model\ResourceModel\Redemption;

use Eguana\Redemption\Api\Data\RedemptionInterface;
use Eguana\Redemption\Model\Redemption as RedemptionModel;
use Eguana\Redemption\Model\ResourceModel\AbstractCollection;
use Eguana\Redemption\Model\ResourceModel\Redemption as RedemptionResourceModel;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Collection for Redemption Model And Resource Model
 *
 * Class Collection
 */
class Collection extends AbstractCollection implements SearchResultInterface
{
    /**
     * @var string
     */
    protected $_idFieldName = 'redemption_id';

    /**
     * Redemption prefix
     *
     * @var string
     */
    private $redemptionPrefix = 'redemption_redemption_collection';

    /**
     * Redemption object
     *
     * @var string
     */
    private $redemptionObject = 'redemption_collection';

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
            RedemptionModel::class,
            RedemptionResourceModel::class
        );
        $this->_map['fields']['store'] = 'store_table.store_id';
        $this->_map['fields']['redemption_id'] = 'main_table.redemption_id';
        $this->_map['fields']['counter'] = 'counter_table.offline_store_id';
        $this->_map['fields']['counter_seats'] = 'counter_table.offline_store_id';
    }

    /**
     * Add filter by store
     *
     * @param array|int|Store $store
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
     * Set Items List
     *
     * @param array|null $items
     * @return $this|Collection
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Get Aggregations
     *
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * Set Aggregations
     *
     * @param AggregationInterface $aggregations
     * @return Collection|void
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Get Search Criteria
     *
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set Search Criteria
     *
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return $this|Collection
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get Total Count
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set Total Count
     *
     * @param int $totalCount
     * @return $this|Collection
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Options method
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('redemption_id', 'title');
    }

    /**
     * Retrieve all ids for collection
     *
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
     * After load method
     *
     * @return Collection
     * @throws \Exception
     */
    protected function _afterLoad()
    {
        try {
            $entityMetadata = $this->metadataPool->getMetadata(RedemptionInterface::class);
            $this->performAfterLoad('eguana_redemption_store', $entityMetadata->getLinkField());
            $this->performAfterLoadForCounter('eguana_redemption_counter', $entityMetadata->getLinkField());
            return parent::_afterLoad();
        } catch (Exception $exception) {
            $this->logger->info($exception->getMessage());
        }
        return parent::_afterLoad();
    }

    /**
     * Join store relation table if there is store filter
     */
    protected function _renderFiltersBefore()
    {
        try {
            $entityMetadata = $this->metadataPool->getMetadata(RedemptionInterface::class);
            $this->joinStoreRelationTable('eguana_redemption_store', $entityMetadata->getLinkField());
        } catch (Exception $exception) {
            $this->logger->info($exception->getMessage());
        }
    }
}
