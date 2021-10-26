<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2021-01-27
 * Time: 오전 10:32
 */

namespace Eguana\InventoryCompensation\Controller\Adminhtml\System\Config;

use Eguana\InventoryCompensation\Logger\Logger;
use Eguana\InventoryCompensation\Model\GetReservationOrder;
use Eguana\InventoryCompensation\Model\Source\Config;
use Eguana\InventoryCompensation\Model\SourceCancelDeduction;
use Eguana\InventoryCompensation\Model\SourceRefundDeduction;
use Eguana\InventoryCompensation\Model\SourceShipmentDeduction;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;

/**
 * Config class to perform cron action
 *
 * Class InventoryCompensationManager
 */
class InventoryCompensationManager extends Action
{
    /**
     * @var GetReservationOrder
     */
    private $getReservationOrder;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var Json
     */
    private $json;

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
     * @var Logger
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * InventoryCompensationManager constructor.
     * @param Action\Context $context
     * @param GetReservationOrder $getReservationOrder
     * @param JsonFactory $jsonFactory
     * @param Json $json
     * @param SourceShipmentDeduction $sourceShipmentDeduction
     * @param SourceRefundDeduction $sourceRefundDeduction
     * @param SourceCancelDeduction $sourceCancelDeduction
     * @param Logger $logger
     * @param Config $config
     */
    public function __construct(
        Action\Context $context,
        GetReservationOrder $getReservationOrder,
        JsonFactory $jsonFactory,
        Json $json,
        SourceShipmentDeduction $sourceShipmentDeduction,
        SourceRefundDeduction $sourceRefundDeduction,
        SourceCancelDeduction $sourceCancelDeduction,
        Logger $logger,
        Config $config
    ) {
        parent::__construct($context);
        $this->getReservationOrder = $getReservationOrder;
        $this->jsonFactory = $jsonFactory;
        $this->json = $json;
        $this->sourceShipmentDeduction = $sourceShipmentDeduction;
        $this->sourceRefundDeduction = $sourceRefundDeduction;
        $this->sourceCancelDeduction = $sourceCancelDeduction;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * To add missing entries inventory_reservation table
     *
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Json|ResultInterface
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
                            $this->logger->info('Order Id ' . $orderEntityId. ' ' .$exception->getMessage());
                        }
                    }
                }

                return $this->jsonFactory->create()->setData(['success' => true]);

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
