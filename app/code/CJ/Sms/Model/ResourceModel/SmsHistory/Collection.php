<?php
declare(strict_types=1);

namespace CJ\Sms\Model\ResourceModel\SmsHistory;

use CJ\Sms\Model\SmsHistory as SmsHistoryModel;
use CJ\Sms\Model\ResourceModel\SmsHistory as SmsHistoryResourceModel;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Store\Model\Store;

class Collection extends AbstractCollection implements SearchResultInterface
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

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
            SmsHistoryModel::class,
            SmsHistoryResourceModel::class
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
     * @return \Eguana\EventManager\Model\ResourceModel\EventManager\Collection|SearchResultInterface
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param array|null $items
     * @return \Eguana\EventManager\Model\ResourceModel\EventManager\Collection|SearchResultInterface
     */
    public function setItems(array $items = null)
    {
        return $this;
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
     * @return \Eguana\EventManager\Model\ResourceModel\EventManager\Collection
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }


    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition);
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = false)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
            $this->setFlag('store_filter_added', true);
        }
        return $this;
    }

    /**
     * Perform adding filter by store
     *
     * @param int|array|Store $store
     * @param bool $withAdmin
     * @return void
     */
    protected function performAddStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof Store) {
            $store = [$store->getId()];
        }

        if (!is_array($store)) {
            $store = [$store];
        }

        if ($withAdmin) {
            $store[] = Store::DEFAULT_STORE_ID;
        }

        $this->addFilter('store_id', ['in' => $store], 'public');
    }

}
