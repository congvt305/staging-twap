<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Model;

use Eguana\Faq\Api\Data\FaqInterface as FaqInterfaceAlias;
use Eguana\Faq\Api\Data\FaqInterfaceFactory as FaqInterfaceFactoryAlias;
use Eguana\Faq\Api\Data\FaqSearchResultsInterface as FaqSearchResultsInterfaceAlias;
use Eguana\Faq\Api\FaqRepositoryInterface;
use Eguana\Faq\Api\Data;
use Eguana\Faq\Model\ResourceModel\Faq as ResourceFaq;
use Eguana\Faq\Model\ResourceModel\Faq\Collection as CollectionAlias;
use Eguana\Faq\Model\ResourceModel\Faq\CollectionFactory as FaqCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface as SearchCriteriaInterfaceAlias;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterfaceAlias;

/**
 * Class FaqRepository
 *
 * Eguana\Faq\Model
 */
class FaqRepository implements FaqRepositoryInterface
{
    /**
     * @var ResourceFaq
     */
    private $resource;

    /**
     * @var FaqFactory
     */
    private $faqFactory;

    /**
     * @var FaqCollectionFactory
     */
    private $faqCollectionFactory;

    /**
     * @var Data\FaqSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var FaqInterfaceFactoryAlias
     */
    private $dataFaqFactory;

    /**
     * @var StoreManagerInterfaceAlias
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param ResourceFaq $resource
     * @param FaqFactory $faqFactory
     * @param Data\FaqInterfaceFactory $dataFaqFactory
     * @param FaqCollectionFactory $faqCollectionFactory
     * @param Data\FaqSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterfaceAlias $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceFaq $resource,
        FaqFactory $faqFactory,
        FaqInterfaceFactoryAlias $dataFaqFactory,
        FaqCollectionFactory $faqCollectionFactory,
        Data\FaqSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterfaceAlias $storeManager,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resource = $resource;
        $this->faqFactory = $faqFactory;
        $this->faqCollectionFactory = $faqCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataFaqFactory = $dataFaqFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save Block data
     *
     * @param FaqInterfaceAlias $faq
     * @return Faq
     * @throws CouldNotSaveException
     */
    public function save(Data\FaqInterface $faq)
    {
        if (empty($faq->getStoreId())) {
            $faq->setStoreId($this->storeManager->getStore()->getId());
        }

        try {
            $this->resource->save($faq);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $faq;
    }

    /**
     * Load Block data by given Block Identity
     *
     * @param string $faqId
     * @return Faq
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($faqId)
    {
        $faq = $this->faqFactory->create();
        $this->resource->load($faq, $faqId);
        if (!$faq->getId()) {
            throw new NoSuchEntityException(__('Faq with id "%1" does not exist.', $faqId));
        }
        return $faq;
    }

    /**
     * Load Block data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param SearchCriteriaInterfaceAlias $criteria
     * @return FaqSearchResultsInterfaceAlias
     */
    public function getList(SearchCriteriaInterfaceAlias $criteria)
    {
        $searchResults = $this->searchResults($criteria);
        return $searchResults;
    }

    /**
     * Delete Block
     *
     * @param FaqInterfaceAlias $faq
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\FaqInterface $faq)
    {
        try {
            $this->resource->delete($faq);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Faq by given Block Identity
     *
     * @param string $faqId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($faqId)
    {
        return $this->delete($this->getById($faqId));
    }

    /**
     * @param SearchCriteriaInterfaceAlias $criteria
     * @return Data\FaqSearchResultsInterface
     */
    private function searchResults(SearchCriteriaInterfaceAlias $criteria)
    {
        /** @var CollectionAlias $collection */
        $collection = $this->faqCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var Data\FaqSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
