<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 30/9/20
 * Time: 06:48 PM
 */
namespace Eguana\NewsBoard\Model;

use Eguana\NewsBoard\Api\Data\NewsInterface;
use Eguana\NewsBoard\Api\Data\NewsInterfaceFactory;
use Eguana\NewsBoard\Api\Data\NewsSearchResultsInterface;
use Eguana\NewsBoard\Api\Data\NewsSearchResultsInterfaceFactory;
use Eguana\NewsBoard\Api\NewsRepositoryInterface;
use Eguana\NewsBoard\Model\ResourceModel\News as ResourceNews;
use Eguana\NewsBoard\Model\ResourceModel\News\Collection;
use Eguana\NewsBoard\Model\ResourceModel\News\CollectionFactory as NewsCollectionFactory;
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
 * Class NewsRepository to perform CRUD operations
 */
class NewsRepository implements NewsRepositoryInterface
{
    /**
     * @var array
     */
    private $instances = [];

    /**
     * @var ResourceNews
     */
    private $resource;

    /**
     * @var NewsCollectionFactory
     */
    private $newsCollectionFactory;

    /**
     * @var NewsSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var NewsInterfaceFactory
     */
    private $newsInterfaceFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * NewsRepository constructor.
     * @param ResourceNews $resource
     * @param NewsCollectionFactory $newsCollectionFactory
     * @param NewsSearchResultsInterfaceFactory $newsSearchResultsInterfaceFactory
     * @param NewsInterfaceFactory $newsInterfaceFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        ResourceNews $resource,
        NewsCollectionFactory $newsCollectionFactory,
        NewsSearchResultsInterfaceFactory $newsSearchResultsInterfaceFactory,
        NewsInterfaceFactory $newsInterfaceFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->resource = $resource;
        $this->newsCollectionFactory = $newsCollectionFactory;
        $this->searchResultsFactory = $newsSearchResultsInterfaceFactory;
        $this->newsInterfaceFactory = $newsInterfaceFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param NewsInterface $news
     * @return NewsInterface|AbstractModel
     * @throws CouldNotSaveException
     */
    public function save(NewsInterface $news)
    {
        try {
            /** @var NewsInterface|AbstractModel $news */
            $this->resource->save($news);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the data: %1',
                $exception->getMessage()
            ));
        }
        return $news;
    }

    /**
     * @param $newsId
     * @return NewsInterface|AbstractModel
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($newsId)
    {
        /** @var NewsInterface|AbstractModel $data */
        $data = $this->newsInterfaceFactory->create();
        $this->resource->load($data, $newsId);
        if (!$data->getId()) {
            throw new NoSuchEntityException(__('Requested news doesn\'t exist'));
        }
        return $data;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return NewsSearchResultsInterface|mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->newsCollectionFactory->create();
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

            if ($filter->getField() == 'news_id') {
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
     * @param NewsInterface $news
     * @return bool
     * @throws StateException
     */
    public function delete(NewsInterface $news)
    {
        /** @var NewsInterface|AbstractModel $news */
        $id = $news->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($news);
        } catch (Exception $e) {
            throw new StateException(
                __('Unable to remove data %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * @param $newsId
     * @return bool
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($newsId)
    {
        $news = $this->getById($newsId);
        return $this->delete($news);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection $collection
     * @return NewsSearchResultsInterface
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
