<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 26/10/20
 * Time: 06:00 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Model;

use Eguana\EventReservation\Api\Data\UserReservationInterface;
use Eguana\EventReservation\Api\Data\UserReservationSearchResultsInterface;
use Eguana\EventReservation\Api\Data\UserReservationSearchResultsInterfaceFactory;
use Eguana\EventReservation\Api\UserReservationRepositoryInterface;
use Eguana\EventReservation\Model\ResourceModel\UserReservation as UserReservationResource;
use Eguana\EventReservation\Model\ResourceModel\UserReservation\Collection;
use Eguana\EventReservation\Model\ResourceModel\UserReservation\CollectionFactory as UserReservationCollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * UserReservationRepository class CRUD operations
 *
 * Class UserReservationRepository
 */
class UserReservationRepository implements UserReservationRepositoryInterface
{
    /**
     * @var UserReservationResource
     */
    private $userReservationResource;

    /**
     * @var UserReservationFactory
     */
    private $userReservationFactory;

    /**
     * @var UserReservationCollectionFactory
     */
    private $userReservationCollectionFactory;

    /**
     * @var UserReservationSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @param UserReservationResource $userReservationResource
     * @param UserReservationFactory $userReservationFactory
     * @param UserReservationCollectionFactory $userReservationCollectionFactory
     * @param UserReservationSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        UserReservationResource $userReservationResource,
        UserReservationFactory $userReservationFactory,
        UserReservationCollectionFactory $userReservationCollectionFactory,
        UserReservationSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->userReservationFactory = $userReservationFactory;
        $this->userReservationResource = $userReservationResource;
        $this->userReservationCollectionFactory = $userReservationCollectionFactory;
    }

    /**
     * Save UserReservation Reservation data
     *
     * @param UserReservationInterface $userReservation
     * @return UserReservationInterface|mixed
     * @throws CouldNotSaveException
     */
    public function save(UserReservationInterface $userReservation)
    {
        try {
            $this->userReservationResource->save($userReservation);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the reservation: %1', $exception->getMessage()),
                $exception
            );
        }
        return $userReservation;
    }

    /**
     * Load UserReservation data by given Id
     *
     * @param int $userReservationId
     * @return UserReservationInterface|UserReservation
     * @throws NoSuchEntityException
     */
    public function getById($userReservationId)
    {
        $userReservation = $this->userReservationFactory->create();
        $userReservation->load($userReservationId);
        if (!$userReservation->getId()) {
            throw new NoSuchEntityException(
                __('The Reservation with the "%1" ID doesn\'t exist.', $userReservationId)
            );
        }
        return $userReservation;
    }

    /**
     * Load UserReservation data collection by given search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return UserReservationSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria) : UserReservationSearchResultsInterface
    {
        /** @var Collection $collection */
        $collection = $this->userReservationCollectionFactory->create();

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
     * @return UserReservationSearchResultsInterface
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
     * Delete UserReservation
     *
     * @param UserReservationInterface $userReservation
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(UserReservationInterface $userReservation) : bool
    {
        try {
            $this->userReservationResource->delete($userReservation);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the reservation: %1', $exception->getMessage())
            );
        }
        return true;
    }

    /**
     * Delete UserReservation by given Id
     *
     * @param int $userReservationId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($userReservationId) : bool
    {
        try {
            return $this->delete($this->getById($userReservationId));
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the reservation by id: %1', $exception->getMessage())
            );
        }
    }
}
