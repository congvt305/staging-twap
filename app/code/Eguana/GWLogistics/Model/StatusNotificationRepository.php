<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/28/20
 * Time: 4:14 PM
 */

namespace Eguana\GWLogistics\Model;

use Eguana\GWLogistics\Api\Data\StatusNotificationInterface;
use Eguana\GWLogistics\Api\Data\StatusNotificationSearchResultInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class StatusNotificationRepository implements \Eguana\GWLogistics\Api\StatusNotificationRepositoryInterface
{
    /**
     * @var \Eguana\GWLogistics\Api\Data\StatusNotificationInterfaceFactory
     */
    private $statusNotificationInterfaceFactory;
    /**
     * @var ResourceModel\StatusNotification
     */
    private $statusNotificationResource;
    /**
     * @var ResourceModel\StatusNotification\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \Eguana\GWLogistics\Api\Data\StatusNotificationSearchResultInterfaceFactory
     */
    private $searchResultInterfaceFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    public function __construct(
        \Eguana\GWLogistics\Api\Data\StatusNotificationInterfaceFactory $statusNotificationInterfaceFactory,
        \Eguana\GWLogistics\Model\ResourceModel\StatusNotification $statusNotificationResource,
        \Eguana\GWLogistics\Model\ResourceModel\StatusNotification\CollectionFactory $collectionFactory,
        \Eguana\GWLogistics\Api\Data\StatusNotificationSearchResultInterfaceFactory $searchResultInterfaceFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->statusNotificationInterfaceFactory = $statusNotificationInterfaceFactory;
        $this->statusNotificationResource = $statusNotificationResource;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultInterfaceFactory = $searchResultInterfaceFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    public function getById(int $id): StatusNotificationInterface
    {
        $statusNotification = $this->statusNotificationInterfaceFactory->create();
        try {
            $this->statusNotificationResource->load($statusNotification, $id);
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Status Notification with id "%1" does not exist.', $id));

        }
        return $statusNotification;
    }

    /**
     * @param StatusNotificationInterface $reverseStatusNotification
     * @return StatusNotificationInterface
     */
    public function save(StatusNotificationInterface $reverseStatusNotification): StatusNotificationInterface
    {
        try {
            /** @var \Eguana\GWLogistics\Model\StatusNotification $reverseStatusNotification */
            $this->statusNotificationResource->save($reverseStatusNotification);
            return $reverseStatusNotification;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save status notification.'), $e);
        }
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return StatusNotificationSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): StatusNotificationSearchResultInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        /** @var StatusNotificationSearchResultInterface $searchResult */
        $searchResult = $this->searchResultInterfaceFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems);
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }
}
