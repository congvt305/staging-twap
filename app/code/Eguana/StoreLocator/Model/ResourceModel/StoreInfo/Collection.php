<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 06/17/20
 * Time: 12:00 PM
 */
namespace Eguana\StoreLocator\Model\ResourceModel\StoreInfo;

use Eguana\StoreLocator\Model\ResourceModel\StoreInfo as ResourceModel;
use Eguana\StoreLocator\Model\StoreInfo as Model;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DB\Select as SelectAlias;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Collection class for store info
 *
 * Class Collection
 *  Eguana\StoreLocator\Model\ResourceModel\StoreInfo
 */
class Collection extends AbstractCollection implements SearchResultInterface
{
    protected $aggregations;
    protected $_idFieldName = 'entity_id';

    /**
     * constructor
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }

    /**
     * getter
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * setter
     * @param AggregationInterface $aggregations
     * @return SearchResultInterface|void
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Retrieve all ids for collection
     * Backward compatibility with EAV collection
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * Get search criteria.
     *
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param ExtensibleDataInterface[] $items
     * @return array|ExtensibleDataInterface[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $items;
    }

    /**
     * This function will return collection of store under the radius
     * @param $currentLocationPoint
     * @param $radius
     */
    public function addDistance($currentLocationPoint, $radius)
    {
        if (!empty($currentLocationPoint)) {
            $select = $this->getSelect();
            $this->addExpressionFieldToSelect(
                'distance',
                '(111.111 *
            DEGREES(ACOS(LEAST(1.0, COS(RADIANS(SUBSTRING_INDEX({{location}},",",1)))
                * COS(RADIANS({{latitude}}))
                * COS(RADIANS(SUBSTRING_INDEX({{location}},",",-1) - {{longitude}}))
                + SIN(RADIANS(SUBSTRING_INDEX({{location}},",",1)))
                * SIN(RADIANS({{latitude}}))))))',
                [
                    'location'=> 'location',
                    'latitude'=>$currentLocationPoint['lat'],
                    'longitude'=>$currentLocationPoint['long']
                ]
            );
            $select->having("distance <= ?", 50);
        }
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @return SelectAlias
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $select = clone $this->getSelect();
        $select->reset(SelectAlias::ORDER);
        $select->reset(SelectAlias::LIMIT_COUNT);
        $select->reset(SelectAlias::LIMIT_OFFSET);

        $countSelect = $this->_conn->select();
        $countSelect->from(['s' => $select]);
        $countSelect->reset(SelectAlias::COLUMNS);
        $countSelect->columns(new \Zend_Db_Expr('COUNT(*)'));
        return $countSelect;
    }
}
