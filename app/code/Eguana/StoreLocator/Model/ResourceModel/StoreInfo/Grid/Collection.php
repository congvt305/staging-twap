<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 15/7/20
 * Time: 6:17 PM
 */
namespace Eguana\StoreLocator\Model\ResourceModel\StoreInfo\Grid;

use Magento\Framework\Api\ExtensibleDataInterface as ExtensibleDataInterfaceAlias;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Eguana\StoreLocator\Model\ResourceModel\StoreInfo\Collection as StoreLocatorCollection;
use Magento\Framework\Api\SearchCriteriaInterface as SearchCriteriaInterfaceAlias;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategyInterfaceAlias;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactoryInterfaceAlias;
use Magento\Framework\DB\Adapter\AdapterInterface as AdapterInterfaceAlias;
use Magento\Framework\EntityManager\MetadataPool as MetadataPoolAlias;
use Magento\Framework\Event\ManagerInterface as ManagerInterfaceAlias;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb as AbstractDbAlias;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document as DocumentAlias;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterfaceAlias;
use Psr\Log\LoggerInterface as LoggerInterfaceAlias;

/**
 * Collection for displaying grid of cms blocks
 */
class Collection extends StoreLocatorCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * @param EntityFactoryInterfaceAlias $entityFactory
     * @param LoggerInterfaceAlias $logger
     * @param FetchStrategyInterfaceAlias $fetchStrategy
     * @param ManagerInterfaceAlias $eventManager
     * @param StoreManagerInterfaceAlias $storeManager
     * @param MetadataPoolAlias $metadataPool
     * @param string $mainTable
     * @param string $eventPrefix
     * @param string $eventObject
     * @param string $resourceModel
     * @param string $model
     * @param string|null $connection
     * @param AbstractDbAlias $resource
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EntityFactoryInterfaceAlias $entityFactory,
        LoggerInterfaceAlias $logger,
        FetchStrategyInterfaceAlias $fetchStrategy,
        ManagerInterfaceAlias $eventManager,
        StoreManagerInterfaceAlias $storeManager,
        MetadataPoolAlias $metadataPool,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = DocumentAlias::class,
        AdapterInterfaceAlias $connection = null,
        AbstractDbAlias $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $metadataPool,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
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
     * @return SearchCriteriaInterfaceAlias|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param SearchCriteriaInterfaceAlias $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(SearchCriteriaInterfaceAlias $searchCriteria = null)
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
     * @param ExtensibleDataInterfaceAlias[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
