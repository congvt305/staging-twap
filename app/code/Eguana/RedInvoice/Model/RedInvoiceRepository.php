<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 5/11/21
 * Time: 10:10 AM
 */
declare(strict_types=1);

namespace Eguana\RedInvoice\Model;

use Eguana\RedInvoice\Api\RedInvoiceRepositoryInterface;
use Eguana\RedInvoice\Api\Data\RedInvoiceSearchResultsInterface;
use Eguana\RedInvoice\Api\Data\RedInvoiceSearchResultsInterfaceFactory;
use Eguana\RedInvoice\Model\ResourceModel\RedInvoice as ResourceRedInvoice;
use Eguana\RedInvoice\Model\ResourceModel\RedInvoice\Collection;
use Eguana\RedInvoice\Model\ResourceModel\RedInvoice\CollectionFactory as RedInvoiceCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

/**
 * This class used for the repository methods which interacts with the database
 * class RedInvoiceRepository
 */
class RedInvoiceRepository implements RedInvoiceRepositoryInterface
{
    /**
     * @var ResourceRedInvoice
     */
    private $resourceRedInvoice;

    /**
     * @var RedInvoiceFactory
     */
    private $redInvoiceFactory;

    /**
     * @var RedInvoiceCollectionFactory
     */
    private $redInvoiceCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var RedInvoiceSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RedInvoiceRepository constructor.
     *
     * @param CollectionProcessorInterface $collectionProcessor
     * @param RedInvoiceSearchResultsInterfaceFactory $searchResultsFactory
     * @param RedInvoiceCollectionFactory $redInvoiceCollectionFactory
     * @param RedInvoiceFactory $redInvoiceFactory
     * @param ResourceRedInvoice $resourceRedInvoice
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        RedInvoiceSearchResultsInterfaceFactory $searchResultsFactory,
        RedInvoiceCollectionFactory $redInvoiceCollectionFactory,
        RedInvoiceFactory $redInvoiceFactory,
        ResourceRedInvoice $resourceRedInvoice,
        LoggerInterface $logger
    ) {
        $this->resourceRedInvoice = $resourceRedInvoice;
        $this->redInvoiceFactory = $redInvoiceFactory;
        $this->redInvoiceCollectionFactory = $redInvoiceCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->logger = $logger;
    }

    /**
     * Save RedInvoice
     *
     * @param $redInvoice
     * @return RedInvoice
     * @throws CouldNotSaveException
     */
    public function save($redInvoice)
    {
        try {
            $this->resourceRedInvoice->save($redInvoice);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the red invoice info: %1', $exception->getMessage()),
                $exception
            );
        }
        return $redInvoice;
    }

    /**
     * Load data by given id
     *
     * @param string $redInvoiceId
     * @return RedInvoice
     */
    public function getById($redInvoiceId)
    {
        $redInvoice = $this->redInvoiceFactory->create();
        $this->resourceRedInvoice->load($redInvoice, $redInvoiceId);
        return $redInvoice;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return RedInvoiceSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->redInvoiceCollectionFactory->create();

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
     * @return RedInvoiceSearchResultsInterface
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
     * Delete RedInvoice
     *
     * @param $redInvoice
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete($redInvoice) : bool
    {
        try {
            $this->resourceRedInvoice->delete($redInvoice);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the red invoice info: %1', $exception->getMessage())
            );
        }
        return true;
    }

    /**
     * Delete RedInvoice by given id
     *
     * @param int $redInvoiceId
     * @return bool
     */
    public function deleteById($redInvoiceId) : bool
    {
        try {
            $result = $this->delete($this->getById($redInvoiceId));
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $result;
    }
}
