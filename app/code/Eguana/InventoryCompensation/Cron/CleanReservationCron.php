<?php

namespace Eguana\InventoryCompensation\Cron;

use Eguana\InventoryCompensation\Logger\Logger;
use Eguana\InventoryCompensation\Model\GetReservationOrder;
use Eguana\InventoryCompensation\Model\Source\Config;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;

/**
 * Cron class to clean reservation
 *
 * Class CleanReservationCron
 */
class CleanReservationCron
{
    /**
     * @var GetReservationOrder
     */
    private $getReservationOrder;

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

    private $_numberNeedClean = 2000;

    /**
     * CleanReservationCron constructor.
     *
     * @param Json $json
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        GetReservationOrder $getReservationOrder,
        Json $json,
        Config $config,
        Logger $logger
    ) {
        $this->getReservationOrder = $getReservationOrder;
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
            $errorFlag = false;
            // Delete All Compensation Orders
            $this->getReservationOrder->deleteAllCompensationOrders();

            // Get All Reservation Orders
            $reservationOrders = $this->getReservationOrder->getReservationOrders();
            if ($loggerActive) {
                $this->logger->info("RESERVATION ORDER LIST");
                $this->logger->info($this->json->serialize($reservationOrders));
            }

            // Delete Reservation Orders has status: shipment_processing, complete, closed, canceled
            $index = 0;
            $numberNeedClean = $this->config->getNumbersOrderClean();
            if (!$numberNeedClean) {
                $numberNeedClean = $this->_numberNeedClean;
            }
            foreach ($reservationOrders as $reservationOrder) {
                $metadata = $this->json->unserialize($reservationOrder['metadata']);
                if ($metadata['object_type'] == 'order' && $metadata['event_type'] == 'order_placed') {
                    try {
                        $orderEntityId = '';
                        if (isset($metadata['object_id'])) {
                            $orderEntityId = $metadata['object_id'];
                        }
                        $entityIdByIncrementId = false;
                        if (empty($orderEntityId) && isset($metadata['object_increment_id'])) {
                            $orderEntityId = $metadata['object_increment_id'];
                            $entityIdByIncrementId = true;
                        }
                        if ($loggerActive) {
                            $this->logger->info($index . ".CLEAN ORDER ID: " . $orderEntityId);
                        }
                        /* @var Order $order */
                        if ($entityIdByIncrementId) {
                            $order = $this->getReservationOrder->getOrderByIncrementId($orderEntityId);
                        } else{
                            $order = $this->getReservationOrder->getOrder($orderEntityId);
                        }
                        if ($order && $order->getId()) {
                            $orderStatus = $order->getStatus();
                            if ($loggerActive) {
                                $this->logger->info('order status: ' . $orderStatus);
                            }

                            $resultDelete = $this->getReservationOrder->deleteReservationByOrder($orderEntityId, $orderStatus, $entityIdByIncrementId);

                            if ($loggerActive) {
                                if ($resultDelete) {
                                    $this->logger->info("DELETING RESERVATION ORDER ID: " . $orderEntityId);
                                } else {
                                    $this->logger->info("PROCESSING RESERVATION ORDER ID: " . $orderEntityId);
                                }
                            }
                            $index++;
                        }  else {
                            if ($loggerActive) {
                                $this->logger->info("NOT FOUND ORDER ID: " . $orderEntityId);
                            }
                            if ($orderEntityId) {
                                $resultDelete = $this->getReservationOrder->deleteReservationByOrder($orderEntityId, 'complete', $entityIdByIncrementId);
                                if ($loggerActive) {
                                    if ($resultDelete) {
                                        $this->logger->info("DELETING RESERVATION ORDER ID: " . $orderEntityId);
                                    } else {
                                        $this->logger->info("PROCESSING RESERVATION ORDER ID: " . $orderEntityId);
                                    }
                                }
                            }
                        }
                    } catch (\Exception $exception) {
                        if ($loggerActive) {
                            $this->logger->info('Order Id ' . $orderEntityId . ' has error' . $exception->getMessage());
                        }
                    }
                }
                if ($index >= $numberNeedClean) {
                    break;
                }
            }
        }
    }
}
