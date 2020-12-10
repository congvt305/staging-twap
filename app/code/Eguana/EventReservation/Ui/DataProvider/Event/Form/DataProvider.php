<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 20/10/20
 * Time: 11:50 AM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Ui\DataProvider\Event\Form;

use Eguana\EventReservation\Api\UserReservationRepositoryInterface;
use Eguana\EventReservation\Model\ResourceModel\Event\CollectionFactory;
use Eguana\EventReservation\Model\UserReservation;
use Eguana\EventReservation\Model\UserReservation\ReservationValidation;
use Magento\Catalog\Model\Category\FileInfo;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * This class is used to get add/edit form data
 *
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FileInfo
     */
    private $fileInfo;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ReservationValidation
     */
    private $reservationValidation;

    /**
     * @var UserReservationRepositoryInterface
     */
    private $userReservationRepository;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param FileInfo $fileInfo
     * @param CollectionFactory $eventCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface $storeManagerInterface
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ReservationValidation $reservationValidation
     * @param UserReservationRepositoryInterface $userReservationRepository
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        FileInfo $fileInfo,
        CollectionFactory $eventCollectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManagerInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ReservationValidation $reservationValidation,
        UserReservationRepositoryInterface $userReservationRepository,
        array $meta = [],
        array $data = []
    ) {
        $this->fileInfo = $fileInfo;
        $this->collection = $eventCollectionFactory->create();
        $this->storeManager = $storeManagerInterface;
        $this->dataPersistor = $dataPersistor;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->reservationValidation = $reservationValidation;
        $this->userReservationRepository = $userReservationRepository;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        /** @var CollectionFactory $items */
        $items = $this->collection->getItems();
        foreach ($items as $event) {
            $thumbnail = $event->getData('thumbnail');
            if ($thumbnail) {
                $thumbnailImagePath = explode('/', $thumbnail);
                $thumbnailImageCount = count($thumbnailImagePath);
                $url = $this->storeManager->getStore()
                        ->getBaseUrl('media') . $thumbnail;
                $stat = $this->fileInfo->getStat($url);
                $thumbnailImage = [
                    'url'   => $url,
                    'file'  => $thumbnailImagePath[$thumbnailImageCount - 1],
                    'size'  => isset($stat['size']) ? $stat['size'] : 0
                ];
                $event->setData('thumbnail', [$thumbnailImage]);
            }

            if ($event->getId()) {
                $reserved = $this->checkReservedSeats($event->getId());

                $this->loadedData[$event->getId()] = $event->getData();
                if ($reserved) {
                    $this->loadedData[$event->getId()]['disabled'] = true;
                } else {
                    $this->loadedData[$event->getId()]['disabled'] = false;
                }
            }
        }

        $data = $this->dataPersistor->get('event_reservation_form');
        if (!empty($data)) {
            $event = $this->collection->getNewEmptyItem();
            $event->setData($data);
            $reserved = $this->checkReservedSeats($event->getId());
            $this->loadedData[$event->getId()] = $event->getData();
            if ($reserved) {
                $this->loadedData[$event->getId()]['disabled'] = true;
            } else {
                $this->loadedData[$event->getId()]['disabled'] = false;
            }
            $this->dataPersistor->clear('event_reservation_form');
        }
        return $this->loadedData;
    }

    /**
     * Check reserved seats against event id
     *
     * @param $eventId
     */
    private function checkReservedSeats($eventId)
    {
        $storeIds = $this->reservationValidation->availableCountersForEvent($eventId);
        if (!in_array(0, $storeIds)) {
            $storeIds[] = 0;
        }
        $storeIds = implode(',', $storeIds);
        $search = $this->searchCriteriaBuilder
            ->addFilter('main_table.status', UserReservation::STATUS_CANCELED, 'neq')
            ->addFilter('main_table.event_id', $eventId, 'eq')
            ->addFilter('main_table.offline_store_id', $storeIds, 'in')
            ->create();
        return $this->userReservationRepository->getList($search)->getTotalCount();
    }
}
