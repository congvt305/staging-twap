<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 15/10/20
 * Time: 12:40 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Model;

use Eguana\EventReservation\Api\Data\EventInterface;
use Eguana\EventReservation\Api\Data\EventSearchResultsInterfaceFactory;
use Eguana\EventReservation\Api\EventRepositoryInterface;
use Eguana\EventReservation\Model\ResourceModel\Event as EventResource;
use Eguana\EventReservation\Model\ResourceModel\Event\Collection;
use Eguana\EventReservation\Model\ResourceModel\Event\CollectionFactory as EventCollectionFactory;
use Eguana\EventReservation\Api\Data\EventSearchResultsInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * EventRepository class CRUD operations
 *
 * Class EventRepository
 */
class EventRepository implements EventRepositoryInterface
{
    /**
     * @var EventResource
     */
    private $eventResource;

    /**
     * @var EventFactory
     */
    private $eventFactory;

    /**
     * @var EventCollectionFactory
     */
    private $eventCollectionFactory;

    /**
     * @var EventSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param EventResource $eventResource
     * @param EventFactory $eventFactory
     * @param EventCollectionFactory $eventCollectionFactory
     * @param EventSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        EventResource $eventResource,
        EventFactory $eventFactory,
        EventCollectionFactory $eventCollectionFactory,
        EventSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->eventResource = $eventResource;
        $this->eventFactory = $eventFactory;
        $this->eventCollectionFactory = $eventCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save Event Reservation data
     *
     * @param EventInterface $event
     * @return EventInterface|mixed
     * @throws CouldNotSaveException
     */
    public function save(EventInterface $event)
    {
        try {
            $this->eventResource->save($event);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the event: %1', $exception->getMessage()),
                $exception
            );
        }
        return $event;
    }

    /**
     * Load Event data by given Id
     *
     * @param int $eventId
     * @return EventInterface|Event
     * @throws NoSuchEntityException
     */
    public function getById($eventId)
    {
        $event = $this->eventFactory->create();
        $event->load($eventId);
        if (!$event->getId()) {
            throw new NoSuchEntityException(__('The Event with the "%1" ID doesn\'t exist.', $eventId));
        }
        return $event;
    }

    /**
     * Load Event data collection by given search criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @return EventSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria) : EventSearchResultsInterface
    {
        /** @var Collection $collection */
        $collection = $this->eventCollectionFactory->create();

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

        /** @var EventSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete Event
     *
     * @param EventInterface $event
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(EventInterface $event) : bool
    {
        try {
            $this->eventResource->delete($event);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the event: %1', $exception->getMessage())
            );
        }
        return true;
    }

    /**
     * Delete Event by given Id
     *
     * @param int $eventId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($eventId) : bool
    {
        try {
            return $this->delete($this->getById($eventId));
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the event by id: %1', $exception->getMessage())
            );
        }
    }
}
