<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2021-01-29
 * Time: 오후 3:16
 */

namespace Eguana\InventoryCompensation\Cron;

use Eguana\InventoryCompensation\Logger\Logger;
use Eguana\InventoryCompensation\Model\GetReservationOrder;
use Eguana\InventoryCompensation\Model\Source\Config;
use Eguana\InventoryCompensation\Model\SourceCancelDeduction;
use Eguana\InventoryCompensation\Model\SourceRefundDeduction;
use Eguana\InventoryCompensation\Model\SourceShipmentDeduction;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;

/**
 * Cron class to deduct source items
 *
 * Class InventoryCompensationManager
 */
class InventoryCompensationCron
{
    /**
     * @var GetReservationOrder
     */
    private $getReservationOrder;

    /**
     * @var SourceShipmentDeduction
     */
    private $sourceShipmentDeduction;

    /**
     * @var SourceRefundDeduction
     */
    private $sourceRefundDeduction;

    /**
     * @var SourceCancelDeduction
     */
    private $sourceCancelDeduction;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * InventoryCompensationCron constructor.
     * @param GetReservationOrder $getReservationOrder
     * @param SourceShipmentDeduction $sourceShipmentDeduction
     * @param SourceRefundDeduction $sourceRefundDeduction
     * @param SourceCancelDeduction $sourceCancelDeduction
     * @param Json $json
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        GetReservationOrder $getReservationOrder,
        SourceShipmentDeduction $sourceShipmentDeduction,
        SourceRefundDeduction $sourceRefundDeduction,
        SourceCancelDeduction $sourceCancelDeduction,
        Json $json,
        Config $config,
        Logger $logger
    ) {
        $this->getReservationOrder = $getReservationOrder;
        $this->sourceShipmentDeduction = $sourceShipmentDeduction;
        $this->sourceRefundDeduction = $sourceRefundDeduction;
        $this->sourceCancelDeduction = $sourceCancelDeduction;
        $this->json = $json;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * To deduct source items add missing entries inventory_reservation table
     *
     * @return void
     */
    public function execute()
    {
        $inventoryCompensationActive = $this->config->getActive();
        $loggerActive = $this->config->getLoggerActive();
        if ($inventoryCompensationActive) {

                $reservationOrders = $this->getReservationOrder->getReservationOrders();

                if ($loggerActive) {
                    $this->logger->info("RESERVATION ORDER LIST");
                    $this->logger->info($this->json->serialize($reservationOrders));
                }

                foreach ($reservationOrders as $reservationOrder) {
                    $metadata = $this->json->unserialize($reservationOrder['metadata']);

                    if ($metadata['object_type'] == 'order' && $metadata['event_type'] == 'order_placed') {
                        try {
                        $orderEntityId = $metadata['object_id'];
                        /** @var Order $order */
                        $order = $this->getReservationOrder->getOrder($orderEntityId);
                        if ($order) {
                            $orderStatus = $order->getStatus();
                            $compensationOrder = $this->getReservationOrder->getCompensationOrder($orderEntityId);

                            if ($loggerActive) {
                                $this->logger->info("COMPENSATION ORDER");
                                $this->logger->info($this->json->serialize($compensationOrder));
                            }

                            $this->deductSourceItem($compensationOrder, $orderStatus, $order);
                        }
                        } catch (\Exception $exception) {
                            $this->logger->info($exception->getMessage());
                        }
                    }
                }
        }
    }

    /**
     * Deduct source item and add entry in inventory_reservation table
     *
     * @param array $compensationOrder
     * @param float|null $orderStatus
     * @param Order $order
     */
    public function deductSourceItem($compensationOrder, $orderStatus, $order)
    {
        if ($compensationOrder === false) {
            if (($orderStatus == 'shipment_processing' || $orderStatus == 'complete') && $order->hasShipments()) {
                $this->sourceShipmentDeduction->shipmentDeduction($order);
            } elseif ($orderStatus == 'closed') {
                if ($order->hasShipments()) {
                    $this->sourceShipmentDeduction->shipmentDeduction($order);
                } elseif ($order->hasCreditmemos() && !$order->hasShipments()) {
                    $this->sourceRefundDeduction->refundDeduction($order);
                }
            } elseif ($orderStatus == 'canceled') {
                $this->sourceCancelDeduction->cancelDeduction($order);
            }
        }
    }
}
