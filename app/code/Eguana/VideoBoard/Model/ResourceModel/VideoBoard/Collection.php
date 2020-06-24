<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 9/6/20
 * Time: 8:07 PM
 */
namespace Eguana\VideoBoard\Model\ResourceModel\VideoBoard;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Eguana\VideoBoard\Model\VideoBoard as VideoBoardModel;
use Eguana\VideoBoard\Model\ResourceModel\VideoBoard as VideoBoardResourceModel;
use Magento\Framework\Api\Search\AggregationInterface;

/**
 * Collection class to retrieve the data from db table
 *
 * Class Collection
 */
class Collection extends AbstractCollection implements SearchResultInterface
{
    /**
     * @var string
     */
    private $idFieldName = 'entity_id';

    /**
     * Event prefix
     *
     * @var string
     */
    private $eventPrefix = 'videoboard_howto_grid_collection';

    /**
     * Event object
     *
     * @var string
     */
    private $eventObject = 'videoboard_howto_grid_collection';

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
            VideoBoardModel::class,
            VideoBoardResourceModel::class
        );
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
     *
     * @param AggregationInterface $aggregations
     *
     * @return $this
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
     *
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
     * @param SearchCriteriaInterface|null $searchCriteria
     *
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
     *
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
     *
     * @return $this|SearchResultInterface
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
