<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2021-01-27
 * Time: 오전 11:28
 */

namespace Eguana\InventoryCompensation\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryReservationsApi\Model\ReservationInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * To Get Reservation Orders
 *
 * Class GetReservationOrder
 */
class GetReservationOrder
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param ResourceConnection $resource
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ResourceConnection $resource,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->resource = $resource;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Get Reservation Orders which are placed
     *
     * @return array
     */
    public function getReservationOrders()
    {
        $connection = $this->resource->getConnection();
        $reservationTable = $this->resource->getTableName('inventory_reservation');

        $select = $connection->select()
            ->from(
                $reservationTable,
                [ReservationInterface::QUANTITY => 'SUM(' . ReservationInterface::QUANTITY . ')', 'metadata']
            )
            ->group('metadata');

        return $connection->fetchAll($select);
    }

    /**
     * For checking compensation order exists against order id
     *
     * @param $orderId
     * @return array
     */
    public function getCompensationOrder($orderId)
    {
        $connection = $this->resource->getConnection();

        $query = "SELECT SUM(quantity) AS quantity, metadata FROM inventory_reservation
                WHERE metadata LIKE '%" . "\"$orderId\"%' AND metadata NOT LIKE \"%order_placed%\" GROUP BY metadata";

        return $connection->fetchRow($query);
    }

    /**
     * Get order details by order id
     *
     * @param $orderId
     * @return OrderInterface
     */
    public function getOrder($orderId)
    {
        $order = [];
        try {
            return $this->orderRepository->get($orderId);
        } catch (\Exception $e) {
            return $order;
        }
    }
}
