<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 21/10/20
 * Time: 11:00 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Model;

use Eguana\EventReservation\Api\CounterRepositoryInterface;
use Eguana\EventReservation\Api\Data\CounterInterface;
use Eguana\EventReservation\Api\Data\CounterSearchResultsInterface;
use Eguana\EventReservation\Api\Data\CounterSearchResultsInterfaceFactory;
use Eguana\EventReservation\Model\ResourceModel\Counter as CounterResource;
use Eguana\EventReservation\Model\ResourceModel\Counter\Collection;
use Eguana\EventReservation\Model\ResourceModel\Counter\CollectionFactory as CounterCollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * CounterRepository class CRUD operations
 *
 * Class CounterRepository
 */
class CounterRepository implements CounterRepositoryInterface
{
    /**
     * @var CounterResource
     */
    private $counterResource;

    /**
     * @var CounterFactory
     */
    private $counterFactory;

    /**
     * @var CounterCollectionFactory
     */
    private $counterCollectionFactory;

    /**
     * @var CounterSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @param CounterResource $counterResource
     * @param CounterFactory $counterFactory
     * @param CounterCollectionFactory $counterCollectionFactory
     * @param CounterSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        CounterResource $counterResource,
        CounterFactory $counterFactory,
        CounterCollectionFactory $counterCollectionFactory,
        CounterSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->counterFactory = $counterFactory;
        $this->counterResource = $counterResource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->counterCollectionFactory = $counterCollectionFactory;
    }

    /**
     * Save Counter Reservation data
     *
     * @param CounterInterface $counter
     * @return CounterInterface|mixed
     * @throws CouldNotSaveException
     */
    public function save(CounterInterface $counter)
    {
        try {
            $this->counterResource->save($counter);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the counter: %1', $exception->getMessage()),
                $exception
            );
        }
        return $counter;
    }

    /**
     * Load Counter data by given Id
     *
     * @param int $counterId
     * @return CounterInterface|Counter
     * @throws NoSuchEntityException
     */
    public function getById($counterId)
    {
        $counter = $this->counterFactory->create();
        $counter->load($counterId);
        if (!$counter->getId()) {
            throw new NoSuchEntityException(
                __('The Counter with the "%1" ID doesn\'t exist.', $counterId)
            );
        }
        return $counter;
    }

    /**
     * Load Counter data collection by given search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return CounterSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria) : CounterSearchResultsInterface
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
     * Add Filters To Collection
     *
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
     * Add Sort Orders To Collection
     *
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
     * Add Paging To Collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     */
    private function addPagingToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
    }

    /**
     * Build Search Result
     *
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
     * Delete Counter
     *
     * @param CounterInterface $counter
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(CounterInterface $counter) : bool
    {
        try {
            $this->counterResource->delete($counter);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the counter: %1', $exception->getMessage())
            );
        }
        return true;
    }

    /**
     * Delete Counter by given Id
     *
     * @param int $counterId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($counterId) : bool
    {
        try {
            return $this->delete($this->getById($counterId));
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the counter by id: %1', $exception->getMessage())
            );
        }
    }
}
