<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: danish
 * Date: 11/26/19
 * Time: 5:33 PM
 */
namespace Eguana\StoreLocator\Model;

use Eguana\StoreLocator\Api\Data\StoreInfoInterface;
use Eguana\StoreLocator\Api\Data\StoreInfoSearchResultInterface;
use Eguana\StoreLocator\Api\Data\StoreInfoSearchResultInterfaceFactory;
use Eguana\StoreLocator\Api\StoreInfoRepositoryInterface;
use Eguana\StoreLocator\Model\ResourceModel\StoreInfo as StoreInfoResourceModel;
use Eguana\StoreLocator\Model\ResourceModel\StoreInfo\Collection;
use Eguana\StoreLocator\Model\ResourceModel\StoreInfo\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Model
 *
 * Class StoreInfoRepository
 *  Eguana\StoreLocator\Model
 */
class StoreInfoRepository implements StoreInfoRepositoryInterface
{
    /**
     * @var StoreInfoFactory
     */

    private $StoreInfoFactory;

    /**
     * @var StoreInfoResourceModel
     */
    private $StoreInfoResourceModel;

    /**
     * @var StoreInfoSearchResultInterfaceFactory
     */
    private $StoreInfoSearchResultInterfaceFactory;

    /**
     * @var CollectionFactory $historyCollectionFactory
     */
    private $historyCollectionFactory;

    /**
     * @var StoreInfoSearchResultInterfaceFactory $searchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var SearchCriteriaBuilder $searchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * StoreInfoRepository constructor.
     * @param StoreInfoResourceModel $StoreInfoResourceModel
     * @param StoreInfoFactory $StoreInfoFactory
     * @param CollectionFactory $historyCollectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreInfoSearchResultInterfaceFactory $StoreInfoSearchResultInterfaceFactory
     */
    public function __construct(
        StoreInfoResourceModel $StoreInfoResourceModel,
        StoreInfoFactory $StoreInfoFactory,
        CollectionFactory $historyCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreInfoSearchResultInterfaceFactory $StoreInfoSearchResultInterfaceFactory
    ) {
        $this->StoreInfoResourceModel = $StoreInfoResourceModel;
        $this->StoreInfoFactory = $StoreInfoFactory;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->searchResultFactory = $StoreInfoSearchResultInterfaceFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param int $id
     * @return StoreInfoInterface|StoreInfo
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $history = $this->StoreInfoFactory->create();
        $this->StoreInfoResourceModel->load($history, $id);
        if (!$history->getId()) {
            throw new NoSuchEntityException(__('Unable to find history with ID "%1"', $id));
        }
        return $history;
    }

    /**
     * @param $customerId
     * @return StoreInfoInterface[]
     */
    public function getCustomerById($customerId)
    {
        $criteriaBuilder = $this->searchCriteriaBuilder;
        $criteriaBuilder->addFilter('customer_id', ['eq' => $customerId]);
        $StoreInfo = $this->getList($criteriaBuilder->create());
        return $StoreInfo->getItems(); // return an array
    }

    /**
     * @param StoreInfo $StoreInfo
     * @return void
     * @throws AlreadyExistsException
     */
    public function save($StoreInfo)
    {
        $this->StoreInfoResourceModel->save($StoreInfo);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return StoreInfoSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->historyCollectionFactory->create();

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
     * @return StoreInfoSearchResultInterface
     */
    private function buildSearchResult(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
