<?php

namespace CJ\VLogicOrder\Model;

use CJ\VLogicOrder\Logger\Logger;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use CJ\VLogicOrder\Model\Request\CreateOrder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterfaceFactory;
use Magento\Sales\Model\Order\Shipment\TrackFactory as ShipmentTrackFactory;

class ShipmentActivity
{

    const SHIPMENT_ACTIVITY_V_LOGIC_PATCH = 'vlogic/shipment_activity_vlogic_cron/enable';

    const STATUS_DELIVERY_COMPLETE = 'G01';

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var OrderFactory
     */
    private $orderFatory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var CreateOrder
     */
    private $createOrder;

    /**
     * @var ShipmentTrackCreationInterfaceFactory
     */
    private $shipmentTrackCreationInterfaceFactory;

    /**
     * @var ShipmentTrackFactory
     */
    private $shipmentTrackFactory;

    /**
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param Logger $logger
     * @param OrderRepository $orderRepository
     * @param OrderFactory $orderFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param CreateOrder $createOrder
     * @param Json $json
     * @param ShipmentTrackCreationInterfaceFactory $shipmentTrackCreationInterfaceFactory
     * @param ShipmentTrackFactory $shipmentTrackFactory
     */
    public function __construct(
        OrderCollectionFactory                $orderCollectionFactory,
        Logger                                $logger,
        OrderRepository                       $orderRepository,
        OrderFactory                          $orderFactory,
        ScopeConfigInterface                  $scopeConfig,
        CreateOrder                           $createOrder,
        Json                                  $json,
        ShipmentTrackCreationInterfaceFactory $shipmentTrackCreationInterfaceFactory,
        ShipmentTrackFactory                  $shipmentTrackFactory
    ){
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->orderFatory = $orderFactory;
        $this->json = $json;
        $this->scopeConfig = $scopeConfig;
        $this->createOrder = $createOrder;
        $this->shipmentTrackCreationInterfaceFactory = $shipmentTrackCreationInterfaceFactory;
        $this->shipmentTrackFactory = $shipmentTrackFactory;
    }

    /**
     * @param $orderId
     * @return void
     */
    public function execute($orderId = null)
    {
        $response = [];
        if ($this->scopeConfig->getValue(self::SHIPMENT_ACTIVITY_V_LOGIC_PATCH, 'default', null)) {
            $allowOrderStatuses = [
                'shipment_processing'
            ];
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->addFieldToFilter('status', ['in' => $allowOrderStatuses]);
            $orderCollection->addFieldToFilter('sent_to_vlogic', 1);
            if (!empty($orderId)) {
                $orderCollection->addFieldToFilter('entity_id', $orderId);
            }
            $orderCollection->setPageSize(100)->setCurPage(1);
            if ($orderCollection->getSize()) {
                $response = $this->sendOrder($orderCollection);
            } else {
                $response['message'] = 'NO ORDERS TO TRACK SHIPMENT ACTIVITY SEND';
                $this->logger->info('NO ORDERS TO TRACK SHIPMENT ACTIVITY SEND');
            }
        }
        return $response;
    }

    /**
     * @param OrderCollection $orderCollection
     * @return array|bool|float|int|mixed|string|null
     */
    private function sendOrder(OrderCollection $orderCollection)
    {
        $this->logger->info('=====Start cron track shipment activity VLogic=====');
        $response = [];
        /** @var Order $order */
        $data = [];
        $data['Name'] = "ShipmentActivity";
        $data['Parameters'] = [];
        try {
            foreach ($orderCollection->getItems() as $order) {
                $data['Parameters'][] = [
                    "Name" => "CustomerOrderNumbers",
                    "Value" => $order->getIncrementId()
                ];
            }
            $this->logger->info('request body to track shipment activity: ');
            $this->logger->info($this->json->serialize($data));
            $response = $this->createOrder->receiveShipmentActivity($data, $order->getStoreId());
            $this->logger->info('response from api track shipment activity: ');
            $this->logger->info($this->json->serialize($response));

            if (!empty($response["Message"])) {
                $this->logger->info($response["Message"]);
            } elseif (!empty($response['Storer']['Branches']) && is_array($response['Storer']['Branches'])) {
                foreach ($response['Storer']['Branches'] as $branches) {
                    if (!empty($branches['Shipments']) && is_array($branches['Shipments'])) {
                        foreach ($branches['Shipments'] as $shipment) {
                            if (!empty($shipment['CustomerOrderNumber']) && $shipment['LastStatusCode'] == self::STATUS_DELIVERY_COMPLETE) {
                                try {
                                    $order = $this->orderFatory->create()->loadByIncrementId($shipment['CustomerOrderNumber']);
                                    if ($order && $order->getId()) {
                                        $shipmentOrder = $order->getShipmentsCollection()->getFirstItem();
                                        if ($shipmentOrder && $shipmentOrder->getId()) {
                                            $vLogicTrack = $this->createTrackNo($order, $shipment['DeliveryNoteNumber']);
                                            $dataTrack = [
                                                'carrier_code' => $vLogicTrack->getCarrierCode(),
                                                'title' => $vLogicTrack->getTitle(),
                                                'number' => $vLogicTrack->getTrackNumber()
                                            ];
                                            $newDataTrack = $this->shipmentTrackFactory->create()->addData($dataTrack);
                                            $shipmentOrder->addTrack($newDataTrack)->save();
                                        }
                                        $order->setData('ignore_process', 1);
                                        $order->setStatus('delivery_complete');
                                        $order->setState('delivery_complete');
                                        $order->setData('receive_track_number_vlogic', 1);
                                        $this->orderRepository->save($order);
                                    }
                                } catch (\Exception $e) {
                                    $this->logger->critical('vlogic | track shipment activity failed: ', [$e->getMessage()]);
                                    continue;
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical('vlogic | track shipment activity failed: order id ', [$order->getId()]);
            $this->logger->error($e->getMessage());
        }
        $this->logger->info('=====End cron track shipment activity VLogic=====');

        return $response;
    }

    /**
     * @param $order
     * @param string $trackingNum
     * @return Order\Shipment\TrackCreation
     */
    private function createTrackNo($order, string $trackingNum = '')
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
