<?php declare(strict_types=1);

namespace CJ\NinjaVanShipping\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use CJ\NinjaVanShipping\Logger\Logger as NinjaVanShippingLogger;
use Magento\Sales\Api\ShipOrderInterface;
use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Shipping\Model\ShipmentNotifier;
use Magento\Sales\Api\Data\ShipmentTrackInterfaceFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Sales\Model\Order;
use CJ\NinjaVanShipping\Model\Request\CreateShipment;
use Magento\Framework\Serialize\Serializer\Json;
use CJ\NinjaVanShipping\Helper\Data as NinjaVanHelper;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface as OrderDataInterface;
use Magento\Sales\Model\Order\Shipment\TrackFactory as ShipmentTrackFactory;

class CreateShipmentAutomatically implements ObserverInterface
{
    const TRACKING_NUMBER_FREFIX = 'MYL';
    /**
     * @var NinjaVanShippingLogger
     */
    private $logger;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var ConvertOrder
     */
    protected $convertOrder;

    /**
     * @var ShipmentNotifier
     */
    protected $shipmentNotifier;

    /**
     * @var ShipmentTrackInterfaceFactory
     */
    protected $trackFactory;

    /**
     * @var CarrierFactory
     */
    protected $carrierFactory;

    /**
     * @var ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var CreateShipment
     */
    protected $createShipment;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var NinjaVanHelper
     */
    private $ninjavanHelper;

    /**
     * @var ShipmentItemCreationInterfaceFactory
     */
    private $shipmentItemCreationInterfaceFactory;

    /**
     * @var ShipmentTrackCreationInterfaceFactory
     */
    private $shipmentTrackCreationInterfaceFactory;

    /**
     * @var ShipOrderInterface
     */
    private $shipOrderInterface;

    /**
     * @var ShipmentIdentity
     */
    private $shipmentIdentity;
    /**
     * @var ShipmentTrackFactory
     */
    private ShipmentTrackFactory $shipmentTrackFactory;

    /**
     * CreateShipment constructor
     *
     * @param NinjaVanShippingLogger $logger
     * @param OrderFactory $orderFactory
     * @param ConvertOrder $convertOrder
     * @param ShipmentNotifier $shipmentNotifier
     * @param ShipmentTrackInterfaceFactory $trackFactory
     * @param CarrierFactory $carrierFactory
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param CreateShipment $createShipment
     * @param DateTime $dateTime
     * @param TimezoneInterface $timezone
     * @param Json $json
     * @param NinjaVanHelper $ninjavanHelper
     */
    public function __construct(
        NinjaVanShippingLogger $logger,
        OrderFactory $orderFactory,
        ConvertOrder $convertOrder,
        ShipmentNotifier $shipmentNotifier,
        ShipmentTrackInterfaceFactory $trackFactory,
        CarrierFactory $carrierFactory,
        ShipmentRepositoryInterface $shipmentRepository,
        CreateShipment $createShipment,
        Json $json,
        NinjaVanHelper $ninjavanHelper,
        ShipmentItemCreationInterfaceFactory $shipmentItemCreationInterfaceFactory,
        ShipmentTrackCreationInterfaceFactory $shipmentTrackCreationInterfaceFactory,
        ShipOrderInterface $shipOrder,
        ShipmentIdentity $shipmentIdentity,
        ShipmentTrackFactory $shipmentTrackFactory
    ) {
        $this->logger = $logger;
        $this->orderFactory = $orderFactory;
        $this->convertOrder = $convertOrder;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->trackFactory = $trackFactory;
        $this->carrierFactory = $carrierFactory;
        $this->shipmentRepository = $shipmentRepository;
        $this->createShipment = $createShipment;
        $this->json = $json;
        $this->ninjavanHelper = $ninjavanHelper;
        $this->shipmentItemCreationInterfaceFactory = $shipmentItemCreationInterfaceFactory;
        $this->shipmentTrackCreationInterfaceFactory = $shipmentTrackCreationInterfaceFactory;
        $this->shipOrderInterface = $shipOrder;
        $this->shipmentIdentity = $shipmentIdentity;
        $this->shipmentTrackFactory = $shipmentTrackFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $observer->getEvent()->getData('invoice');
        /** @var \Magento\Sales\Model\Order $order */
        $order = $invoice->getOrder();
        $this->logger->info('ninjavan | event sales_order_invoice_save_after fired: order id ' . $order->getId() . ' with payment method is: ' . $order->getPayment()->getMethod());

        if ($order->getShippingMethod() === 'ninjavan_tablerate') {
            $this->logger->info('ninjavan | start creating shipment: return state ');
            try {
                $this->logger->info('ninjavan | start creating shipment: order id ', [$order->getId()]);
                $this->createShipment($order);
            } catch (\Exception $e) {
                $this->logger->critical('ninjavan | start creating shipment failed: order id ', [$order->getId()]);
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createShipment($order)
    {
        $shipmentItems = $this->createShipmentItem($order);
        if ($shipmentItems == null) {
            return null;
        }
        if (!$order->canShip()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You can\'t create an shipment.')
            );
        }

        try {
            // Send the order's information to NinjaVan to create new delivery order
            $trackNumber = $this->generateTrackNumber($order);
            $data = $this->createShipment->payloadSendToNinjaVan($order, $trackNumber);
            $this->logger->info('request body to create delivery order: ' . $this->json->serialize($data));
            $response = $this->createShipment->requestCreateOrder($data, $order);
            $this->logger->info('response from api create delivery order: ');
            $this->logger->info('ninjavan | response: ', $response);

            $message = 'success';
            if (isset($response['tracking_number'])) {
                $ninjaVanTrack = $this->createTrackNo($order, $response['tracking_number']);
                $shipmentId = $this->shipOrderInterface
                    ->execute(
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
        } catch (\Exception $exception) {
            $this->logger->info("Create delivery order failed: {$exception->getMessage()}");
            return $exception->getMessage();
        }
    }
    /**
     * @param $order
     * @return string
     */
    protected function generateTrackNumber($order): string
    {
        $orderPrefix = self::TRACKING_NUMBER_FREFIX;
        return $orderPrefix.substr($order->getIncrementId(), -6);
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

    /**
     * @param $order
     * @param string $trackingNum
     * @return Order\Shipment\TrackCreation
     */
    public function createTrackNo($order, string $trackingNum = '')
    {
        $carrierCode = (string)$order->getShippingMethod(true)->getCarrierCode();
        $shippingMethod = $order->getShippingMethod();
        if (!$trackingNum) {
            $trackingNum = substr($order->getIncrementId(), -9);
        }
        /** @var \Magento\Sales\Model\Order\Shipment\TrackCreation $trackNo */
        $trackNo = $this->shipmentTrackCreationInterfaceFactory->create();
        $trackNo->setCarrierCode($carrierCode);
        $trackNo->setTitle($shippingMethod);
        $trackNo->setTrackNumber($trackingNum);

        return $trackNo;
    }
}
