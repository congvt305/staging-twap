<?php

namespace Eguana\InventoryCompensation\Controller\Adminhtml\System\Config;

use Eguana\InventoryCompensation\Logger\Logger;
use Eguana\InventoryCompensation\Model\GetReservationOrder;
use Eguana\InventoryCompensation\Model\Source\Config;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;

/**
 * Config class to perform cron action
 *
 * Class CleanReservation
 */
class CleanReservation extends Action
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
     * @var Logger
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    private $_numberNeedClean = 500;

    /**
     * InventoryCompensationManager constructor.
     * @param Action\Context $context
     * @param GetReservationOrder $getReservationOrder
     * @param JsonFactory $jsonFactory
     * @param Json $json
     * @param Logger $logger
     * @param Config $config
     */
    public function __construct(
        Action\Context $context,
        GetReservationOrder $getReservationOrder,
        JsonFactory $jsonFactory,
        Json $json,
        Logger $logger,
        Config $config
    ) {
        parent::__construct($context);
        $this->getReservationOrder = $getReservationOrder;
        $this->jsonFactory = $jsonFactory;
        $this->json = $json;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * To clean entries inventory_reservation table
     *
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Json|ResultInterface
     */
    public function execute()
    {
        $inventoryCompensationActive = $this->config->getActive();
        $loggerActive = $this->config->getLoggerActive();
        $errorFlag = false;
        if ($inventoryCompensationActive) {
            // Delete All Compensation Orders
            $this->getReservationOrder->deleteAllCompensationOrders();

            // Get All Reservation Orders
            $reservationOrders = $this->getReservationOrder->getReservationOrders();
            if ($loggerActive) {
                $this->logger->info("RESERVATION ORDER LIST");
                $this->logger->info($this->json->serialize($reservationOrders));
            }

            // Delete Reservation Orders has status: cleanStatuses
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
                        if ($loggerActive) {
                            $this->logger->info($index . ".CLEAN ORDER ID: " . $orderEntityId);
                        }
                        /* @var Order $order */
                        $order = $this->getReservationOrder->getOrder($orderEntityId);
                        if ($order) {
                            $orderStatus = $order->getStatus();
                            $this->logger->info('order status: '.$orderStatus);

                            $resultDelete = $this->getReservationOrder->deleteReservationByOrder($orderEntityId, $orderStatus);

                            if ($loggerActive) {
                                if ($resultDelete) {
                                    $this->logger->info("DELETING RESERVATION ORDER ID: " . $orderEntityId);
                                } else {
                                    $this->logger->info("PROCESSING RESERVATION ORDER ID: " . $orderEntityId);
                                }
                            }
                            $index++;
                        } else {
                            $this->logger->info("NOT FOUND ORDER ID: " . $orderEntityId);
                        }
                    } catch (\Exception $exception) {
                        $errorFlag = true;
                        $this->logger->info('Order Id ' . $orderEntityId. ' has error' .$exception->getMessage());
                    }
                }

                if ($index >= $numberNeedClean) {
                    break;
                }
            }
            if (!$errorFlag) {
                return $this->jsonFactory->create()->setData(['success' => true]);
            } else {
                return $this->jsonFactory->create()->setData(['success' => false, 'message' => __('Has error. Please check log file.')]);
            }
        }
    }
}
