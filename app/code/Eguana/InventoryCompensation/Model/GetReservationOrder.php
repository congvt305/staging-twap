<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2021-01-27
 * Time: 오전 11:28
 */

namespace Eguana\InventoryCompensation\Model;

use Eguana\InventoryCompensation\Model\Source\Config;
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

    protected $cleanStatuses = ['pending', 'payment_review', 'shipment_processing', 'complete', 'closed', 'canceled', 'delivery_complete'];

    /**
     * @param ResourceConnection $resource
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param Config $config
     */
    public function __construct(
        ResourceConnection $resource,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        Config $config
    ) {
        $this->resource = $resource;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->config = $config;
    }

    /**
     * Get Reservation Orders which are placed
     *
     * @return array
     */
    public function getReservationOrders()
    {
        $connection = $this->resource->getConnection();
        //$reservationTable = $this->resource->getTableName('inventory_reservation');

        //$select = $connection->select()
        //    ->from(
        //        $reservationTable,
        //        [ReservationInterface::QUANTITY => 'SUM(' . ReservationInterface::QUANTITY . ')', 'metadata']
        //    )
        //    ->group('metadata');
        //$query = "SELECT SUM(quantity) AS quantity, metadata FROM inventory_reservation
        //        WHERE metadata LIKE '%" . "\"$orderId\"%' AND metadata NOT LIKE \"%order_placed%\" GROUP BY metadata";
        $query = "SELECT SUM(quantity), metadata FROM inventory_reservation
        WHERE metadata LIKE '%order_placed%' GROUP BY metadata";

        return $connection->fetchAll($query);
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
     * Delete All Compensation Orders
     *
     * @return int
     */
    public function deleteAllCompensationOrders()
    {
        $connection = $this->resource->getConnection();
        $reservationTable = $this->resource->getTableName('inventory_reservation');

        $condition = [ReservationInterface::METADATA . ' NOT LIKE (?)' => "%order_placed%"];
        return $connection->delete($reservationTable, $condition);

    }

    public function deleteReservationByOrder($orderId, $orderStatus)
    {
        $result = 0;

        $cleanStatuses = $this->config->getStatusesNeedClean();
        if ($cleanStatuses) {
            $cleanStatuses = array_map('trim', explode(',', $cleanStatuses));
        } else {
            $cleanStatuses = $this->cleanStatuses;
        }

        if (in_array($orderStatus, $cleanStatuses)) {
            $connection = $this->resource->getConnection();
            $reservationTable = $this->resource->getTableName('inventory_reservation');
            $condition = [ReservationInterface::METADATA . ' LIKE (?)' => "%" . $orderId. "%"];
            $result = $connection->delete($reservationTable, $condition);
        }
        return $result;
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
