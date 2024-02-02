<?php

namespace CJ\NinjaVanShipping\Cron;

use CJ\NinjaVanShipping\Logger\Logger;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface as OrderDataInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterfaceFactory;
use Magento\Sales\Api\ShipOrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use CJ\NinjaVanShipping\Model\Request\CreateShipment;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Shipment\TrackFactory as ShipmentTrackFactory;

class SendOrderToNinjaVan
{
    const TRACKING_NUMBER_FREFIX = 'MYL';
    /**
     * @var OrderCollectionFactory
     */
    private OrderCollectionFactory $orderCollectionFactory;
    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var CreateShipment
     */
    protected $createShipment;

    /**
     * @var ShipmentItemCreationInterfaceFactory
     */
    protected $shipmentItemCreationInterfaceFactory;

    /**
     * @var ShipmentIdentity
     */
    protected ShipmentIdentity $shipmentIdentity;

    /**
     * @var ShipOrderInterface
     */
    protected ShipOrderInterface $shipOrderInterface;

    /**
     * @var ShipmentTrackCreationInterfaceFactory
     */
    protected $shipmentTrackCreationInterfaceFactory;
    /**
     * @var ShipmentTrackFactory
     */
    protected $shipmentTrackFactory;

    /**
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param Logger $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param CreateShipment $createShipment
     * @param Json $json
     * @param ShipmentItemCreationInterfaceFactory $shipmentItemCreationInterfaceFactory
     * @param ShipmentIdentity $shipmentIdentity
     * @param ShipmentTrackCreationInterfaceFactory $shipmentTrackCreationInterfaceFactory
     * @param ShipOrderInterface $shipOrder
     * @param ShipmentTrackFactory $shipmentTrackFactory
     */
    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        Logger $logger,
        ScopeConfigInterface $scopeConfig,
        CreateShipment $createShipment,
        Json $json,
        ShipmentItemCreationInterfaceFactory $shipmentItemCreationInterfaceFactory,
        ShipmentIdentity $shipmentIdentity,
        ShipmentTrackCreationInterfaceFactory $shipmentTrackCreationInterfaceFactory,
        ShipOrderInterface $shipOrder,
        ShipmentTrackFactory $shipmentTrackFactory
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->logger = $logger;
        $this->json = $json;
        $this->scopeConfig = $scopeConfig;
        $this->createShipment = $createShipment;
        $this->shipmentItemCreationInterfaceFactory = $shipmentItemCreationInterfaceFactory;
        $this->shipmentIdentity = $shipmentIdentity;
        $this->shipmentTrackCreationInterfaceFactory = $shipmentTrackCreationInterfaceFactory;
        $this->shipOrderInterface = $shipOrder;
        $this->shipmentTrackFactory = $shipmentTrackFactory;
    }

    public function execute()
    {
        if ($this->scopeConfig->getValue('ninjavan/send_order_to_ninjavan_cron/enable', 'default', null)) {
            $allowOrderStatuses = [
                'processing', 'processing_with_shipment'
            ];
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->addFieldToFilter('status', ['in' => $allowOrderStatuses]);
            $orderCollection->addFieldToFilter('shipping_method', ['eq' => 'ninjavan_tablerate']);
            $orderCollection->addFieldToFilter('sent_to_ninjavan', ['null' => true]);
            if ($orderCollection->getSize()) {
                $this->sendOrder($orderCollection);
            } else {
                $this->logger->info('NO ORDERS TO SEND');
            }
        }
    }

    /**
     * @param OrderCollection $orderCollection
     * @return void
     */
    private function sendOrder(OrderCollection $orderCollection): void
    {
        $this->logger->info('=====Start cron send order to NinjaVan=====');
        /** @var Order $order */
        foreach ($orderCollection->getItems() as $order) {
            try {
                $shipmentItems = $this->createShipmentItem($order);
                if ($shipmentItems == null) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('You can\'t create an shipment.')
                    );
                }

                if (!$order->canShip()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('You can\'t create an shipment.')
                    );
                }
                $trackNumber = $this->createShipment->generateTrackNumber($order);
                // Send the order's information to NinjaVan to create new delivery order
                $data = $this->createShipment->payloadSendToNinjaVan($order, $trackNumber);
                $this->logger->info('request body to create delivery order: ');
                $this->logger->info($this->json->serialize($data));
                $response = $this->createShipment->requestCreateOrder($data, $order);
                $this->logger->info('response from api create delivery order: ');
                $this->logger->info('ninjavan | response: ', $response);

                $message = 'success';

                if (isset($response['tracking_number'])) {
                    $ninjaVanTrack = $this->createTrackNo($order, $response['tracking_number']);
                    $shipmentId = $this->shipOrderInterface->execute(
                        $order->getEntityId(),
                        $shipmentItems,
                        $this->shipmentIdentity->isEnabled(),
                        false,
                        null,
                        [$ninjaVanTrack]
                    );
                    if (empty($shipmentId)) {
                        $this->logger->info("Cannot Create delivery order: {$order->getIncrementId()}");
                        throw new \Exception(__("Cannot Create delivery order: {$order->getIncrementId()}"));
                    }

                    $order->setState('processing');
                    $order->setStatus('processing_with_shipment');
                    $order->setData('sent_to_ninjavan', 1);
                }
                if (isset($response['error'])) {
                    $message = $response['error']['message'];
                    if (isset($response['error']['details'])) {
                        $detail = $this->json->serialize($response['error']['details']);
                        $this->logger->info('ninjavan | detail: ', [$detail]);
                    }
                }
                $this->logger->info('ninjavan | message: ', [$message]);
                $order->save();
            } catch (\Exception $e) {
                $this->logger->critical('ninjavan | start creating shipment failed: order id ', [$order->getId()]);
                $this->logger->error($e->getMessage());
            }
        }
        $this->logger->info('=====End cron send order to NinjaVan=====');
    }

    /**
     * @param $order
     * @param string $trackingNum
     * @return Order\Shipment\TrackCreation
     */
    public function createTrackNo($order, string $trackingNum)
    {
        $carrierCode = (string)$order->getShippingMethod(true)->getCarrierCode();
        $shippingMethod = $order->getShippingMethod();
        /** @var \Magento\Sales\Model\Order\Shipment\TrackCreation $trackNo */
        $trackNo = $this->shipmentTrackCreationInterfaceFactory->create();
        $trackNo->setCarrierCode($carrierCode);
        $trackNo->setTitle($shippingMethod);
        $trackNo->setTrackNumber($trackingNum);

        return $trackNo;
    }

    /**
     * @param OrderDataInterface $order
     * @return array
     */
    public function createShipmentItem($order): array
    {
        $shipmentItems = [];

        $orderItems = $order->getAllVisibleItems();
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($orderItems as $item) {
            /** @var \Magento\Sales\Model\Order\Shipment\ItemCreation $shipmentItem */
            $shipmentItem = $this->shipmentItemCreationInterfaceFactory->create();
            $shipmentItem->setOrderItemId($item->getId())
                ->setQty($item->getQtyOrdered());
            $shipmentItems[] = $shipmentItem;
        }
        return $shipmentItems;
    }
}
