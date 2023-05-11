<?php
declare(strict_types=1);

namespace CJ\Sms\Model;

use CJ\Sms\Api\Data\SmsHistoryInterface;
use CJ\Sms\Api\Data\SmsHistorySearchResultsInterface;
use CJ\Sms\Api\SmsHistoryRepositoryInterface;
use CJ\Sms\Model\ResourceModel\SmsHistory as ResourceSmsHistory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;
use CJ\Sms\Model\SmsHistoryFactory;
use CJ\Sms\Model\ResourceModel\SmsHistory\CollectionFactory;
use CJ\Sms\Api\Data\SmsHistorySearchResultsInterfaceFactory;

class SmsHistoryRepository implements SmsHistoryRepositoryInterface
{
    /**
     * @var ResourceSmsHistory
     */
    private $resourceSmsHistory;

    /**
     * @var SmsHistoryFactory
     */
    private $smsHistoryFactory;

    /**
     * @var CollectionFactory
     */
    private $smsHistoryCollectionFactory;

    /**
     * @var CollectionProcessorInterface|null
     */
    private $collectionProcessor;

    /**
     * @var SmsHistorySearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param ResourceSmsHistory $resourceSmsHistory
     * @param \CJ\Sms\Model\SmsHistoryFactory $smsHistoryFactory
     * @param CollectionFactory $smsHistoryCollectionFactory
     * @param SmsHistorySearchResultsInterfaceFactory $searchResultsFactory
     * @param LoggerInterface $logger
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceSmsHistory $resourceSmsHistory,
        SmsHistoryFactory $smsHistoryFactory,
        CollectionFactory $smsHistoryCollectionFactory,
        SmsHistorySearchResultsInterfaceFactory $searchResultsFactory,
        LoggerInterface $logger,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resourceSmsHistory = $resourceSmsHistory;
        $this->smsHistoryFactory = $smsHistoryFactory;
        $this->smsHistoryCollectionFactory = $smsHistoryCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->logger = $logger;
    }

    /**
     * Save data
     *
     * @param SmsHistoryInterface $smsHistory
     * @return mixed|void
     */
    public function save(SmsHistoryInterface $smsHistory)
    {
        try {
            $this->resourceSmsHistory->save($smsHistory);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $smsHistory;
    }

    /**
     * Get by id
     *
     * @param $entityId
     * @return mixed|void
     */
    public function getById($entityId)
    {
        /**
         * @var SmsHistory $smsHistory
         */
        $smsHistory = $this->smsHistoryFactory->create();
        $this->resourceSmsHistory->load($smsHistory, $entityId);
        return $smsHistory;
    }

    /**
     * Get list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed|void
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ResourceSmsHistory\Collection $collection */
        $collection = $this->smsHistoryCollectionFactory->create();

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), true);
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var SmsHistorySearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete data
     *
     * @param SmsHistoryInterface $smsHistory
     * @return mixed|void
     */
    public function delete(SmsHistoryInterface $smsHistory)
    {
        try {
            $this->resourceSmsHistory->delete($smsHistory);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete by Id
     *
     * @param $entityId
     * @return mixed|void
     */
    public function deleteById($entityId)
    {
        try {
            return $this->delete($this->getById($entityId));
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * Get sms history by phone number
     *
     * @param $phoneNumber
     * @param $storeId
     * @return ResourceSmsHistory\Collection
     */
    public function getByPhoneNumber($phoneNumber, $storeId)
    {
        /** @var ResourceSmsHistory\Collection $collection */
        $collection = $this->smsHistoryCollectionFactory->create();
        $collection->addFieldToFilter(SmsHistoryInterface::TELEPHONE, $phoneNumber)
            ->addFieldToFilter(SmsHistoryInterface::STORE_ID, $storeId);
        return $collection;
    }

}
