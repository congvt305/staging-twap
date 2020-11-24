<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/11/20
 * Time: 7:20 PM
 */
namespace Eguana\Pip\Model\ResourceModel\TerminatedCustomer;

use Eguana\Pip\Api\Data\TerminatedCustomerInterface;
use Eguana\Pip\Model\TerminatedCustomer as TerminatedCustomerModel;
use Eguana\Pip\Model\ResourceModel\TerminatedCustomer as TerminatedCustomerResourceModel;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Collection for TerminatedCustomer Model And Resource Model
 *
 * Class Collection
 */
class Collection extends AbstractCollection implements SearchResultInterface
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Terminated Customer prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'eguana_pip_terminatedcustomer_collection';

    /**
     * Terminated Customer object
     *
     * @var string
     */
    protected $_eventObject = 'terminatedcustomer_collection';

    /**
     * @var
     */
    private $aggregations;

    /**
     * Initialization of Model and ResourceModel
     */
    protected function _construct()
    {
        $this->_init(
            TerminatedCustomerModel::class,
            TerminatedCustomerResourceModel::class
        );
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
}
