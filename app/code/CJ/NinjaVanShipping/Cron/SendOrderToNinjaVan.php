<?php

namespace CJ\NinjaVanShipping\Cron;

use CJ\NinjaVanShipping\Logger\Logger;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use CJ\NinjaVanShipping\Model\Request\CreateShipment;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SendOrderToNinjaVan
{
    /**
     * @var OrderCollectionFactory
     */
    private OrderCollectionFactory $orderCollectionFactory;
    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var OrderRepository
     */
    private OrderRepository $orderRepository;

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
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param Logger $logger
     * @param OrderRepository $orderRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     */
    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        Logger $logger,
        OrderRepository $orderRepository,
        ScopeConfigInterface $scopeConfig,
        CreateShipment $createShipment,
        Json $json
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->json = $json;
        $this->scopeConfig = $scopeConfig;
        $this->createShipment = $createShipment;
    }

    public function execute()
    {
        if ($this->scopeConfig->getValue('ninjavan/send_order_to_ninjavan_cron/enable', 'default', null)) {
            $allowOrderStatuses = [
                'processing', 'processing_with_shipment'
            ];
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->addFieldToFilter('status', ['in' => $allowOrderStatuses]);
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
            if ($order->getShippingMethod() === 'ninjavan_tablerate') {
                try {
                    /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                    $shipment = $order->getShipmentsCollection()->getFirstItem();
                    if (!$shipment->getId()) {
                        $this->logger->error(__('Order %s does not have shipment', $order->getIncrementId()));
                    }

                    /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
                    $track = $order->getTracksCollection()->getFirstItem();
                    if (!$track->getTrackNumber()) {
                        $this->logger->error(__('Order %s does not have tracking number', $order->getIncrementId()));
                    }
                    // Send the order's information to NinjaVan to create new delivery order
                    $data = $this->createShipment->payloadSendToNinjaVan($order, $shipment, $track);

                    $this->logger->info('request body to create delivery order: ');
                    $this->logger->info($this->json->serialize($data));
                    $request = $this->createShipment->requestCreateOrder($data, $order);
                    $this->logger->info('response from api create delivery order: ');
                    $this->logger->info($this->json->serialize($request));

                    if (isset($request['tracking_number'])) {
                        $order->setData('sent_to_ninjavan', 1);
                        $this->orderRepository->save($order);
                    }
                } catch (\Exception $e) {
                    $this->logger->critical('ninjavan | start creating shipment failed: order id ', [$order->getId()]);
                    $this->logger->error($e->getMessage());
                }
            }
        }
        $this->logger->info('=====End cron send order to NinjaVan=====');
    }
}
