<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 26/10/20
 * Time: 07:25 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Ui\DataProvider\UserReservation\Listing;

use Eguana\EventReservation\Model\ResourceModel\UserReservation\Grid\Collection;
use Eguana\EventReservation\Model\ResourceModel\UserReservation\Grid\CollectionFactory;
use Eguana\EventReservation\Model\UserReservation\ReservationValidation;
use Magento\Framework\Api\Filter;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Data Provider For Listing
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
     * @var ReservationValidation
     */
    private $reservationValidation;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param AuthorizationInterface $authorization
     * @param ReservationValidation $reservationValidation
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
        ReservationValidation $reservationValidation,
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

        $this->request                  = $request;
        $this->collection               = $collectionFactory->create();
        $this->authorization            = $authorization;
        $this->reservationValidation    = $reservationValidation;
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
                'reservations_columns' => [
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
        /** @var Collection $collection */
        $collection = $this->getCollection();

        $data['items'] = [];
        $eventId = $this->request->getParam('event_id');
        if ($eventId) {
            $storeIds = $this->reservationValidation->availableCountersForEvent($eventId);
            $collection->addFieldToFilter('main_table.event_id', $eventId);

            if ($storeIds) {
                $collection->addFieldToFilter('main_table.offline_store_id', ['in' => $storeIds]);
            }

            $data = $collection->toArray();
        }

        return $data;
    }

    /**
     * Add full text search filter to collection
     *
     * @param Filter $filter
     * @return void
     */
    public function addFilter(Filter $filter): void
    {
        if ($filter->getField() !== 'fulltext') {
            $this->collection->addFieldToFilter(
                $filter->getField(),
                [$filter->getConditionType() => $filter->getValue()]
            );
        } else {
            $value = trim($filter->getValue());
            $this->collection->addFieldToFilter(
                [
                    ['attribute' => 'name'],
                    ['attribute' => 'email'],
                    ['attribute' => 'time_slot'],
                    ['attribute' => 'date'],
                    ['attribute' => 'phone']
                ],
                [
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"]
                ]
            );
        }
    }
}
