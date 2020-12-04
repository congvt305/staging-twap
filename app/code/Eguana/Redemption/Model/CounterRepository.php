<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 26/10/20
 * Time: 11:40 PM
 */
declare(strict_types=1);

namespace Eguana\Redemption\Model;

use Eguana\Redemption\Api\CounterRepositoryInterface;
use Eguana\Redemption\Api\Data\CounterSearchResultsInterface;
use Eguana\Redemption\Api\Data\CounterSearchResultsInterfaceFactory;
use Eguana\Redemption\Model\ResourceModel\Counter as ResourceCounter;
use Eguana\Redemption\Model\ResourceModel\Counter\Collection;
use Eguana\Redemption\Model\ResourceModel\Counter\CollectionFactory as CounterCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

/**
 * This class used for the repository methods which interacts with the database
 * class CounterRepository
 */
class CounterRepository implements CounterRepositoryInterface
{
    /**
     * @var ResourceCounter
     */
    private $resourceCounter;

    /**
     * @var CounterFactory
     */
    private $counterFactory;

    /**
     * @var CounterCollectionFactory
     */
    private $counterCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CounterSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CounterRepository constructor.
     *
     * @param CollectionProcessorInterface $collectionProcessor
     * @param CounterSearchResultsInterfaceFactory $searchResultsFactory
     * @param CounterCollectionFactory $counterCollectionFactory
     * @param CounterFactory $counterFactory
     * @param ResourceCounter $resourceCounter
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        CounterSearchResultsInterfaceFactory $searchResultsFactory,
        CounterCollectionFactory $counterCollectionFactory,
        CounterFactory $counterFactory,
        ResourceCounter $resourceCounter,
        LoggerInterface $logger
    ) {
        $this->resourceCounter = $resourceCounter;
        $this->counterFactory = $counterFactory;
        $this->counterCollectionFactory = $counterCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->logger = $logger;
    }

    /**
     * Save counter
     *
     * @param $counter
     * @return Counter
     * @throws CouldNotSaveException
     * @throws AlreadyExistsException
     */
    public function save($counter)
    {
        try {
            $this->resourceCounter->save($counter);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the counter: %1', $exception->getMessage()),
                $exception
            );
        }
        return $counter;
    }

    /**
     * Load data by given id
     *
     * @param string $counterId
     * @return Counter
     */
    public function getById($counterId)
    {
        $counter = $this->counterFactory->create();
        $this->resourceCounter->load($counter, $counterId);
        return $counter;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return CounterSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->counterCollectionFactory->create();

        $this->addFiltersToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);

        $collection->load();

        return $this->buildSearchResult($searchCriteria, $collection);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     */
    private function addFiltersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $fields[] = $filter->getField();
                $conditions[] = [$filter->getConditionType() => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     */
    private function addSortOrdersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        foreach ((array) $searchCriteria->getSortOrders() as $sortOrder) {
            $direction = $sortOrder->getDirection() == SortOrder::SORT_ASC ? 'asc' : 'desc';
            $collection->addOrder($sortOrder->getField(), $direction);
        }
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     */
    private function addPagingToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     * @return CounterSearchResultsInterface
     */
    private function buildSearchResult(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete counter
     *
     * @param $counter
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete($counter) : bool
    {
        try {
            $this->resourceCounter->delete($counter);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the counter: %1', $exception->getMessage())
            );
        }
        return true;
    }

    /**
     * Delete Counter by given id
     *
     * @param int $counterId
     * @return bool
     */
    public function deleteById($counterId) : bool
    {
        try {
            $result = $this->delete($this->getById($counterId));
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $result;
    }
}
