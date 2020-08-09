<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/28/20
 * Time: 4:13 PM
 */

namespace Eguana\GWLogistics\Model;


use Eguana\GWLogistics\Api\Data;
use Eguana\GWLogistics\Api\Data\ReverseStatusNotificationInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class ReverseStatusNotificationRepository implements \Eguana\GWLogistics\Api\ReverseStatusNotificationRepositoryInterface
{
    /**
     * @var Data\ReverseStatusNotificationInterfaceFactory
     */
    private $reverseStatusNotificationInterfaceFactory;
    /**
     * @var ResourceModel\ReverseStatusNotification
     */
    private $reverseStatusNotificationResource;
    /**
     * @var ResourceModel\ReverseStatusNotification\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var Data\ReverseStatusNotificationSearchResultInterfaceFactory
     */
    private $searchResultInterfaceFactory;
    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

    public function __construct(
        \Eguana\GWLogistics\Api\Data\ReverseStatusNotificationInterfaceFactory $reverseStatusNotificationInterfaceFactory,
        \Eguana\GWLogistics\Model\ResourceModel\ReverseStatusNotification $reverseStatusNotificationResource,
        \Eguana\GWLogistics\Model\ResourceModel\ReverseStatusNotification\CollectionFactory $collectionFactory,
        \Eguana\GWLogistics\Api\Data\ReverseStatusNotificationSearchResultInterfaceFactory $searchResultInterfaceFactory,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
    ) {
        $this->reverseStatusNotificationInterfaceFactory = $reverseStatusNotificationInterfaceFactory;
        $this->reverseStatusNotificationResource = $reverseStatusNotificationResource;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultInterfaceFactory = $searchResultInterfaceFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @param int $id
     * @return ReverseStatusNotificationInterface
     */
    public function getById(int $id): ReverseStatusNotificationInterface
    {
        $reverseStatusNotification = $this->reverseStatusNotificationInterfaceFactory->create();
        try {
            $this->reverseStatusNotificationResource->load($reverseStatusNotification, $id);
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Status Notification with id "%1" does not exist.', $id));
        }

        return $reverseStatusNotification;
    }

    /**
     * @param ReverseStatusNotificationInterface $reverseStatusNotification
     * @return ReverseStatusNotificationInterface
     */
    public function save(ReverseStatusNotificationInterface $reverseStatusNotification): ReverseStatusNotificationInterface
    {
        try {
            /** @var \Eguana\GWLogistics\Model\ReverseStatusNotification $reverseStatusNotification */
            $this->reverseStatusNotificationResource->save($reverseStatusNotification);
            return $reverseStatusNotification;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save status notification.'), $e);
        }
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\ReverseStatusNotificationSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): \Eguana\GWLogistics\Api\Data\ReverseStatusNotificationSearchResultInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResult = $this->searchResultInterfaceFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }
}
