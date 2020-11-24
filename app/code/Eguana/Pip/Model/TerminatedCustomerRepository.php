<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/11/20
 * Time: 6:20 PM
 */
namespace Eguana\Pip\Model;

use Eguana\Pip\Api\TerminatedCustomerRepositoryInterface;
use Eguana\Pip\Api\Data\TerminatedCustomerSearchResultsInterface;
use Eguana\Pip\Api\Data\TerminatedCustomerSearchResultsInterfaceFactory;
use Eguana\Pip\Model\ResourceModel\TerminatedCustomer as ResourceTerminatedCustomer;
use Eguana\Pip\Model\ResourceModel\TerminatedCustomer\Collection;
use Eguana\Pip\Model\ResourceModel\TerminatedCustomer\CollectionFactory as TerminatedCustomerCollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

/**
 * @inheritdoc
 */
class TerminatedCustomerRepository implements TerminatedCustomerRepositoryInterface
{
    /**
     * @var ResourceTerminatedCustomer
     */
    private $resourceTerminatedCustomer;

    /**
     * @var TerminatedCustomerFactory
     */
    private $terminatedCustomerFactory;

    /**
     * @var TerminatedCustomerCollectionFactory
     */
    private $terminatedCustomerCollectionFactory;

    /**
     * @var TerminatedCustomerSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * TerminatedCustomerRepository constructor.
     *
     * @param TerminatedCustomerSearchResultsInterfaceFactory $searchResultsFactory
     * @param TerminatedCustomerCollectionFactory $terminatedCustomerCollectionFactory
     * @param TerminatedCustomerFactory $terminatedCustomerFactory
     * @param ResourceTerminatedCustomer $resourceTerminatedCustomer
     * @param LoggerInterface $logger
     */
    public function __construct(
        TerminatedCustomerSearchResultsInterfaceFactory $searchResultsFactory,
        TerminatedCustomerCollectionFactory $terminatedCustomerCollectionFactory,
        TerminatedCustomerFactory $terminatedCustomerFactory,
        ResourceTerminatedCustomer $resourceTerminatedCustomer,
        LoggerInterface $logger
    ) {
        $this->resourceTerminatedCustomer = $resourceTerminatedCustomer;
        $this->terminatedCustomerFactory = $terminatedCustomerFactory;
        $this->terminatedCustomerCollectionFactory = $terminatedCustomerCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->logger = $logger;
    }

    /**
     * Save Terminate Customer
     *
     * @param $terminatedCustomer
     * @return TerminatedCustomer
     * @throws AlreadyExistsException
     * @throws CouldNotSaveException
     */
    public function save($terminatedCustomer)
    {
        try {
            $this->resourceTerminatedCustomer->save($terminatedCustomer);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the Terminated Customer: %1', $exception->getMessage()),
                $exception
            );
        }
        return $terminatedCustomer;
    }

    /**
     * Load data by given id
     *
     * @param string $entityId
     * @return TerminatedCustomer
     */
    public function getById($entityId)
    {
        $terminatedCustomer = $this->terminatedCustomerFactory->create();
        $this->resourceTerminatedCustomer->load($terminatedCustomer, $entityId);
        return $terminatedCustomer;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return TerminatedCustomerSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->terminatedCustomerCollectionFactory->create();

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
     * @return TerminatedCustomerSearchResultsInterface
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
     * Delete Terminated Customer
     *
     * @param $terminatedCustomer
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete($terminatedCustomer) : bool
    {
        try {
            $this->resourceTerminatedCustomer->delete($terminatedCustomer);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the Terminated Customer: %1', $exception->getMessage())
            );
        }
        return true;
    }

    /**
     * Delete Terminated Customer by given id
     *
     * @param int $entityId
     * @return bool
     */
    public function deleteById($entityId) : bool
    {
        try {
            $result = $this->delete($this->getById($entityId));
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $result;
    }
}
