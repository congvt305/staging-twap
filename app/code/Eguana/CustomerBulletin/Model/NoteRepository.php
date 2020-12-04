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

use Eguana\CustomerBulletin\Api\Data\NoteInterface;
use Eguana\CustomerBulletin\Api\Data\NoteInterfaceFactory;
use Eguana\CustomerBulletin\Api\Data\NoteSearchResultsInterface;
use Eguana\CustomerBulletin\Api\Data\NoteSearchResultsInterfaceFactory;
use Eguana\CustomerBulletin\Api\NoteRepositoryInterface;
use Eguana\CustomerBulletin\Model\ResourceModel\Note as ResourceNote;
use Eguana\CustomerBulletin\Model\ResourceModel\Note\Collection;
use Eguana\CustomerBulletin\Model\ResourceModel\Note\CollectionFactory as NoteCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Psr\Log\LoggerInterface;

/**
 * Class NoteRepository to perform CRUD operations
 */
class NoteRepository implements NoteRepositoryInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @var ResourceNote
     */
    protected $resource;

    /**
     * @var NoteCollectionFactory
     */
    protected $noteCollectionFactory;

    /**
     * @var NoteSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var NoteInterfaceFactory
     */
    protected $noteInterfaceFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * NoteRepository constructor.
     *
     * @param ResourceNote $resource
     * @param LoggerInterface $logger
     * @param NoteCollectionFactory $noteCollectionFactory
     * @param NoteSearchResultsInterfaceFactory $noteSearchResultsInterfaceFactory
     * @param NoteInterfaceFactory $noteInterfaceFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        ResourceNote $resource,
        LoggerInterface $logger,
        NoteCollectionFactory $noteCollectionFactory,
        NoteSearchResultsInterfaceFactory $noteSearchResultsInterfaceFactory,
        NoteInterfaceFactory $noteInterfaceFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->resource = $resource;
        $this->noteCollectionFactory = $noteCollectionFactory;
        $this->searchResultsFactory = $noteSearchResultsInterfaceFactory;
        $this->noteInterfaceFactory = $noteInterfaceFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->logger = $logger;
    }

    /**
     * Save note data
     *
     * @param NoteInterface $note
     * @return NoteInterface|AbstractModel
     * @throws CouldNotSaveException
     */
    public function save(NoteInterface $note)
    {
        try {
            /** @var NoteInterface|AbstractModel $note */
            $this->resource->save($note);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the data: %1',
                $exception->getMessage()
            ));
        }
        return $note;
    }

    /**
     * delete note by id
     *
     * @param $noteId
     * @return NoteInterface|AbstractModel|mixed
     * @throws NoSuchEntityException
     */
    public function getById($noteId)
    {
        if (!isset($this->instances[$noteId])) {
            /** @var NoteInterface|AbstractModel $data */
            $data = $this->noteInterfaceFactory->create();
            $this->resource->load($data, $noteId);
            if (!$data->getId()) {
                throw new NoSuchEntityException(__('Requested note doesn\'t exist'));
            }
            $this->instances[$noteId] = $data;
        }
        return $this->instances[$noteId];
    }

    /**
     * get ticketslist by SearchCriteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return NoteSearchResultsInterface|mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->noteCollectionFactory->create();
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
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        Collection $collection
    ) {
        $fields = [];
        $categoryFilter = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $conditionType = $filter->getConditionType() ?: 'eq';

            if ($filter->getField() == 'note_id') {
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
     * @param NoteInterface $note
     * @return bool
     */
    public function delete(NoteInterface $note)
    {
        /** @var NoteInterface|AbstractModel $note */
        $id = $note->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($note);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * delete note by its id
     *
     * @param $noteId
     * @return bool|mixed
     */
    public function deleteById($noteId)
    {
        $note = '';
        try {
            $note = $this->getById($noteId);
            return $this->delete($note);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $note;
    }

    /**
     * use for searchbuilder result
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     * @return NoteSearchResultsInterface
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
