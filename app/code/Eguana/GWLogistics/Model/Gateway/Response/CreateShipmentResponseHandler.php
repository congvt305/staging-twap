<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/10/20
 * Time: 9:37 AM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Gateway\Response;

class CreateShipmentResponseHandler implements \Eguana\GWLogistics\Model\Gateway\Response\HandlerInterface
{
    /**
     * @var \Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory
     */
    private $shipmentItemCreationFactory;
    /**
     * @var \Magento\Sales\Api\Data\ShipmentTrackCreationInterfaceFactory
     */
    private $shipmentTrackCreationFactory;
    /**
     * @var \Magento\Sales\Api\Data\ShipmentCreationArgumentsInterfaceFactory
     */
    private $shipmentCreationArgumentsFactory;
    /**
     * @var \Magento\Sales\Api\Data\ShipmentCreationArgumentsExtensionInterfaceFactory
     */
    private $shipmentCreationArgumentsExtensionFactory;
    /**
     * @var \Magento\Sales\Model\Order\Email\Container\ShipmentIdentity
     */
    private $shipmentIdentity;
    /**
     * @var \Magento\Sales\Api\ShipOrderInterface
     */
    private $shipOrder;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var string
     */
    private $allPayLogisticsID;
    /**
     * @var string
     */
    private $shipmentNo;

    public function __construct(
        \Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory $shipmentItemCreationFactory,
        \Magento\Sales\Api\Data\ShipmentTrackCreationInterfaceFactory $shipmentTrackCreationFactory,
        \Magento\Sales\Api\Data\ShipmentCreationArgumentsInterfaceFactory $shipmentCreationArgumentsFactory,
        \Magento\Sales\Api\Data\ShipmentCreationArgumentsExtensionInterfaceFactory $shipmentCreationArgumentsExtensionFactory,
        \Magento\Sales\Model\Order\Email\Container\ShipmentIdentity $shipmentIdentity,
        \Magento\Sales\Api\ShipOrderInterface $shipOrder,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->shipmentItemCreationFactory = $shipmentItemCreationFactory;
        $this->shipmentTrackCreationFactory = $shipmentTrackCreationFactory;
        $this->shipmentCreationArgumentsFactory = $shipmentCreationArgumentsFactory;
        $this->shipmentCreationArgumentsExtensionFactory = $shipmentCreationArgumentsExtensionFactory;
        $this->shipmentIdentity = $shipmentIdentity;
        $this->shipOrder = $shipOrder;
        $this->logger = $logger;
    }

    public function handle(array $commandSubject, array $response): void
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $commandSubject['order'];
        $createShipmentResponse = $response['createShipmentResponse'];
        $queryLogisticsResponse = $response['queryLogisticsResponse'];
        $this->allPayLogisticsID = $createShipmentResponse['AllPayLogisticsID'];
        $this->shipmentNo = $queryLogisticsResponse['ShipmentNo'];
        $this->createShipment($order);
    }

    private function createShipment(\Magento\Sales\Model\Order $order)
    {
        /** @var \Magento\Sales\Api\Data\ShipmentItemCreationInterface[] $shipmentItems */
        $shipmentItems = $this->buildShipmentItems($order->getAllVisibleItems());
        if (empty($shipmentItems)) {
            return null;
        }
        //should diable notify if system sendmail is not turned on.
        $notify = $this->shipmentIdentity->isEnabled();
        $appendComment = false;
        $comment = null;
        $tracks = $this->buildShipmentTrack($this->shipmentNo);
        $packages = [];
        $arguments = $this->getShipmentCreationArguments();

        $shipmentId = $this->shipOrder->execute(
            $order->getId(),
            $shipmentItems,
            $notify,
            $appendComment,
            $comment,
            [$tracks],
            $packages,
            $arguments
        );
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $items
     * @return array
     */
    private function buildShipmentItems($items)
    {
        /** @var \Magento\Sales\Api\Data\ShipmentItemCreationInterface[] $shipmentItems */
        $shipmentItems = [];
        foreach ($items as $item) {
            $shipmentItem = $this->shipmentItemCreationFactory->create();
            $shipmentItem->setOrderItemId($item->getItemId());
            $shipmentItem->setQty($item->getQtyOrdered());
            $shipmentItems[] = $shipmentItem;
        }
        return $shipmentItems;
    }

    /**
     * @param $shipmentNo
     * @return \Magento\Sales\Api\Data\ShipmentTrackCreationInterface
     */
    private function buildShipmentTrack($shipmentNo)
    {
        /** @var \Magento\Sales\Api\Data\ShipmentTrackCreationInterface $shipmentTrack */
        $shipmentTrack = $this->shipmentTrackCreationFactory->create();
        $shipmentTrack->setCarrierCode('gwlogistics');
        $shipmentTrack->setTitle(__('GWLogistics CVS')); //need to fix to follow config value
        $shipmentTrack->setTrackNumber($shipmentNo);
        return $shipmentTrack;
    }

    private function getShipmentCreationArguments()
    {
        $extensionAttributes = $this->shipmentCreationArgumentsExtensionFactory->create();
        $extensionAttributes->setAllPayLogisticsId($this->allPayLogisticsID);
        $arguments = $this->shipmentCreationArgumentsFactory->create();
        $arguments->setExtensionAttributes($extensionAttributes);
        return $arguments;
    }
}
