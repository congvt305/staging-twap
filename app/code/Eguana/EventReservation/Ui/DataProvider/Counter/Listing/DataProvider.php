<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 20/10/20
 * Time: 10:22 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Ui\DataProvider\Counter\Listing;

use Eguana\EventReservation\Api\EventRepositoryInterface;
use Eguana\EventReservation\Model\ResourceModel\Counter\Grid\Collection;
use Eguana\EventReservation\Model\ResourceModel\Counter\Grid\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Provide data to counter grid
 *
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param AuthorizationInterface $authorization
     * @param DataPersistorInterface $dataPersistor
     * @param EventRepositoryInterface $eventRepository
     * @param StoreManagerInterface $storeManager
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        AuthorizationInterface $authorization,
        DataPersistorInterface $dataPersistor,
        EventRepositoryInterface $eventRepository,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->request = $request;
        $this->collection = $collectionFactory->create();
        $this->authorization = $authorization;
        $this->dataPersistor = $dataPersistor;
        $this->eventRepository = $eventRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Prepares Meta
     *
     * @return array
     */
    public function prepareMetadata()
    {
        $metadata = [];
        if (!$this->authorization->isAllowed('Eguana_EventReservation::event_reservation')) {
            $metadata = [
                'columns' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'editorConfig' => [
                                    'enabled' => false
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }
        return $metadata;
    }

    /**
     * Get grid data
     *
     * @return array
     */
    public function getData(): array
    {
        $eventId = $this->request->getParam('event_id');
        $eventId = $eventId ? $eventId : 0;
        $storeIds = $this->dataPersistor->get('selected_store_id');
        if ($storeIds) {
            $storeIds = [$storeIds];
        }
        $this->dataPersistor->clear('selected_store_id');

        if ($eventId && !$storeIds) {
            $currentEvent = $this->eventRepository->getById($eventId);
            $storeIds = $currentEvent->getStoreId();
        }

        if (!$storeIds || in_array(0, $storeIds)) {
            $storeManagerDataList = $this->storeManager->getStores();
            $storeIds = [];
            foreach ($storeManagerDataList as $key => $value) {
                $storeIds[] = $key;
            }
        }
        $storeIds[] = 0;
        $storeIds = implode(',', $storeIds);

        $this->collection->getSelect()->joinRight(
            ['si' => $this->collection->getTable('storeinfo')],
            'si.entity_id = main_table.offline_store_id AND main_table.event_id = "' .
            $eventId . '"',
            ['entity_id', 'title']
        )->join(
            ['ss' => $this->collection->getTable('eguana_storelocator_store')],
            'ss.entity_id = si.entity_id'
        );
        $this->collection->addFieldToFilter('ss.store_id', ['in' => $storeIds]);
        $this->collection->addFieldToSelect('reservation_counter_id');
        $this->collection->getSelect()->where('si.available_for_events = 1')
            ->group('si.entity_id');
        $this->collection->setOrder('main_table.reservation_counter_id', 'DESC');

        /** @var Collection $collection */
        $collection = $this->getCollection();
        return $collection->toArray();
    }
}
