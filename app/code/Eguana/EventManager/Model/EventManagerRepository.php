<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 11/6/20
 * Time: 11:47 AM
 */
namespace Eguana\EventManager\Model;

use Eguana\EventManager\Api\EventManagerRepositoryInterface;
use Eguana\EventManager\Api\Data;
use Eguana\EventManager\Model\EventManager as EventManagerAlias;
use Eguana\EventManager\Model\ResourceModel\EventManager as ResourceEventManager;
use Eguana\EventManager\Model\ResourceModel\EventManager\CollectionFactory as EventManagerCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Eguana\EventManager\Api\Data\EventManagerInterface;
use Eguana\EventManager\Model\EventManager;
use Magento\Framework\Api\SearchCriteriaInterface;
use Eguana\EventManager\Model\ResourceModel\EventManager\Collection;
use Eguana\EventManager\Api\Data\EventManagerSearchResultsInterfaceFactory;
use Psr\Log\LoggerInterface;

/**
 * This is EventManagerRepository class
 * Class EventManagerRepository
 */
class EventManagerRepository implements EventManagerRepositoryInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ResourceEventManager
     */
    private $resourceEventManager;

    /**
     * @var EventManagerFactory
     */
    private $eventManagerFactory;

    /**
     * @var EventManagerCollectionFactory
     */
    private $eventManagerCollectionFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var EventManagerInterface
     */
    private $dataEventManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var EventManagerSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @param ResourceEventManager $resourceEventManager
     * @param EventManagerFactory $eventManagerFactory
     * @param EventManagerInterface $dataEventManager
     * @param EventManagerCollectionFactory $eventManagerCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param EventManagerSearchResultsInterfaceFactory $searchResultsFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceEventManager $resourceEventManager,
        EventManagerFactory $eventManagerFactory,
        EventManagerInterface $dataEventManager,
        EventManagerCollectionFactory $eventManagerCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        EventManagerSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor = null,
        LoggerInterface $logger
    ) {
        $this->resourceEventManager = $resourceEventManager;
        $this->eventManagerFactory = $eventManagerFactory;
        $this->eventManagerCollectionFactory = $eventManagerCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->logger = $logger;
    }

    /**
     * Save Block data
     * @param EventManagerInterface $eventManager
     * @return EventManagerInterface|mixed
     */
    public function save(EventManagerInterface $eventManager)
    {
        try {
            $this->resourceEventManager->save($eventManager);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $eventManager;
    }

    /**
     * Load Block data by given Block Identity
     *
     * @param $eventManagerId
     * @return EventManagerAlias|mixed
     */
    public function getById($eventManagerId)
    {
        /**
         * @var EventManager $eventManager
         */
        $eventManager = $this->eventManagerFactory->create();
        $this->resourceEventManager->load($eventManager, $eventManagerId);
        return $eventManager;
    }

    /**
     * Load Page data collection by given search criteria
     *
     * @param SearchCriteriaInterface $criteria
     */

    public function getList(SearchCriteriaInterface $criteria)
    {
        /** @var Collection $collection */
        $collection = $this->eventManagerCollectionFactory->create();

        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), true);
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }

        $this->collectionProcessor->process($criteria, $collection);

        /** @var EventManagerSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete Block
     *
     * @param EventManagerInterface $eventManager
     * @return bool
     */
    public function delete(EventManagerInterface $eventManager)
    {
        try {
            $this->resourceEventManager->delete($eventManager);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete EventManager by given Block Identity
     *
     * @param string $eventManagerId
     * @return bool|mixed
     */
    public function deleteById($eventManagerId)
    {
        try {
            return $this->delete($this->getById($eventManagerId));
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }
}
