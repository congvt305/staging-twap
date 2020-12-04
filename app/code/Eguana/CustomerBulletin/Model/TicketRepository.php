<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 30/9/20
 * Time: 06:48 PM
 */
namespace Eguana\CustomerBulletin\Model;

use Eguana\CustomerBulletin\Api\Data\TicketInterface;
use Eguana\CustomerBulletin\Api\Data\TicketInterfaceFactory;
use Eguana\CustomerBulletin\Api\Data\TicketSearchResultsInterface;
use Eguana\CustomerBulletin\Api\Data\TicketSearchResultsInterfaceFactory;
use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Eguana\CustomerBulletin\Model\ResourceModel\Ticket as ResourceTicket;
use Eguana\CustomerBulletin\Model\ResourceModel\Ticket\Collection;
use Eguana\CustomerBulletin\Model\ResourceModel\Ticket\CollectionFactory as TicketCollectionFactory;
use Exception;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Model\AbstractModel;

/**
 * Class TicketRepository to perform CRUD operations
 */
class TicketRepository implements TicketRepositoryInterface
{
    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @var ResourceTicket
     */
    protected $resource;

    /**
     * @var TicketCollectionFactory
     */
    protected $ticketCollectionFactory;

    /**
     * @var TicketSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var TicketInterfaceFactory
     */
    protected $ticketInterfaceFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * TicketRepository constructor.
     * @param ResourceTicket $resource
     * @param TicketCollectionFactory $ticketCollectionFactory
     * @param TicketSearchResultsInterfaceFactory $ticketSearchResultsInterfaceFactory
     * @param TicketInterfaceFactory $ticketInterfaceFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        ResourceTicket $resource,
        TicketCollectionFactory $ticketCollectionFactory,
        TicketSearchResultsInterfaceFactory $ticketSearchResultsInterfaceFactory,
        TicketInterfaceFactory $ticketInterfaceFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->resource = $resource;
        $this->ticketCollectionFactory = $ticketCollectionFactory;
        $this->searchResultsFactory = $ticketSearchResultsInterfaceFactory;
        $this->ticketInterfaceFactory = $ticketInterfaceFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Save ticket data
     *
     * @param TicketInterface $ticket
     * @return TicketInterface|AbstractModel
     * @throws CouldNotSaveException
     */
    public function save(TicketInterface $ticket)
    {
        try {
            /** @var TicketInterface|AbstractModel $ticket */
            $this->resource->save($ticket);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the data: %1',
                $exception->getMessage()
            ));
        }
        return $ticket;
    }

    /**
     * delete ticket by id
     *
     * @param $ticketId
     * @return TicketInterface|AbstractModel|mixed
     * @throws NoSuchEntityException
     */
    public function getById($ticketId)
    {
        /** @var TicketInterface|AbstractModel $data */
        $data = $this->ticketInterfaceFactory->create();
        $this->resource->load($data, $ticketId);
        if (!$data->getId()) {
            throw new NoSuchEntityException(__('Requested ticket doesn\'t exist'));
        }
        return $data;
    }

    /**
     * get ticketslist by SearchCriteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return TicketSearchResultsInterface|mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->ticketCollectionFactory->create();
        $this->addFiltersToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);
        $collection->load();

        return $this->buildSearchResult($searchCriteria, $collection);
    }

    /**
     * set sortorder of collection
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
     * add pagination in collection
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
     * add filter to collection
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
     * Helper function that adds a FilterGroup to the collection.
     *
     * @deprecated 102.0.0
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        Collection $collection
    ) {
        $fields = [];
        $categoryFilter = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $conditionType = $filter->getConditionType() ?: 'eq';

            if ($filter->getField() == 'ticket_id') {
                $categoryFilter[$conditionType][] = $filter->getValue();
                continue;
            }
            $fields[] = ['attribute' => $filter->getField(), $conditionType => $filter->getValue()];
        }

        if ($categoryFilter) {
            $collection->addCategoriesFilter($categoryFilter);
        }

        if ($fields) {
            $collection->addFieldToFilter($fields);
        }
    }

    /**
     * delete note by its model object
     *
     * @param TicketInterface $ticket
     * @return bool
     * @throws StateException
     */
    public function delete(TicketInterface $ticket)
    {
        /** @var TicketInterface|AbstractModel $ticket */
        $id = $ticket->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($ticket);
        } catch (Exception $e) {
            throw new StateException(
                __('Unable to remove data %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * delete note by its id
     *
     * @param $ticketId
     * @return bool|mixed
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function deleteById($ticketId)
    {
        $ticket = $this->getById($ticketId);
        return $this->delete($ticket);
    }

    /**
     * use for searchbuilder result
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     * @return TicketSearchResultsInterface
     */
    private function buildSearchResult(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
