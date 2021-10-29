<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/10/20
 * Time: 5:34 PM
 */
declare(strict_types=1);

namespace Eguana\Redemption\Model;

use Eguana\Redemption\Api\Data\RedemptionSearchResultsInterface;
use Eguana\Redemption\Api\Data\RedemptionSearchResultsInterfaceFactory;
use Eguana\Redemption\Api\RedemptionRepositoryInterface;
use Eguana\Redemption\Model\ResourceModel\Redemption as ResourceRedemption;
use Eguana\Redemption\Model\ResourceModel\Redemption\Collection;
use Eguana\Redemption\Model\ResourceModel\Redemption\CollectionFactory as RedemptionCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * This class used for the repository methods which interacts with the database
 * class RedemptionRepository
 */
class RedemptionRepository implements RedemptionRepositoryInterface
{
    /**
     * @var ResourceRedemption
     */
    private $resourceRedemption;

    /**
     * @var RedemptionFactory
     */
    private $redemptionFactory;

    /**
     * @var RedemptionCollectionFactory
     */
    private $redemptionCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var RedemptionSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RedemptionRepository constructor.
     *
     * @param CollectionProcessorInterface $collectionProcessor
     * @param RedemptionSearchResultsInterfaceFactory $searchResultsFactory
     * @param RedemptionCollectionFactory $redemptionCollectionFactory
     * @param RedemptionFactory $redemptionFactory
     * @param ResourceRedemption $resourceRedemption
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor,
        RedemptionSearchResultsInterfaceFactory $searchResultsFactory,
        RedemptionCollectionFactory $redemptionCollectionFactory,
        RedemptionFactory $redemptionFactory,
        ResourceRedemption $resourceRedemption,
        LoggerInterface $logger
    ) {
        $this->resourceRedemption = $resourceRedemption;
        $this->redemptionFactory = $redemptionFactory;
        $this->redemptionCollectionFactory = $redemptionCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->logger = $logger;
    }

    /**
     * Save redemption
     *
     * @param $redemption
     * @return Redemption
     * @throws CouldNotSaveException|AlreadyExistsException
     */
    public function save($redemption)
    {
        try {
            $this->resourceRedemption->save($redemption);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the redemption: %1', $exception->getMessage()),
                $exception
            );
        }
        return $redemption;
    }

    /**
     * Load data by given id
     *
     * @param int $redemptionId
     * @return \Eguana\Redemption\Api\Data\RedemptionInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getById($redemptionId)
    {
        $redemption = $this->redemptionFactory->create();
        $this->resourceRedemption->load($redemption, $redemptionId);
        if (!$redemption->getId()) {
            $this->logger->debug(__('Redemption ID "%1" not found.', $redemptionId));
            throw new NoSuchEntityException(__('Redemption ID "%1" not found.', $redemptionId));
        }
        return $redemption;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return RedemptionSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->redemptionCollectionFactory->create();

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
     * @return RedemptionSearchResultsInterface
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
     * Delete redemption
     *
     * @param $redemption
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete($redemption) : bool
    {
        try {
            $this->resourceRedemption->delete($redemption);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the redemption: %1', $exception->getMessage())
            );
        }
        return true;
    }

    /**
     * Delete Redemption by given id
     *
     * @param int $redemptionId
     * @return bool
     */
    public function deleteById($redemptionId) : bool
    {
        $result = '';
        try {
            $result = $this->delete($this->getById($redemptionId));
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $result;
    }
}
