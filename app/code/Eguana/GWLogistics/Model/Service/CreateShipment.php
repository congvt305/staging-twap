<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/20/20
 * Time: 8:51 AM
 */

namespace Eguana\GWLogistics\Model\Service;

class CreateShipment
{
    /**
     * @var \Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory
     */
    private $shipmentItemCreationFactory;
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
     * @var \Eguana\GWLogistics\Model\Request\CvsCreateShipmentOrder
     */
    private $cvsCreateShipmentOrder;
    /**
     * @var \Eguana\GWLogistics\Model\Request\QueryLogisticsInfo
     */
    private $queryTransactionInfo;
    /**
     * @var string
     */
    private $allPayLogisticsID;
    /**
     * @var \Magento\Sales\Api\Data\ShipmentTrackCreationInterfaceFactory
     */
    private $shipmentTrackCreationFactory;
    /**
     * @var string
     */
    private $shipmentNo;
    /**
     * @var \Magento\Sales\Api\Data\ShipmentCreationArgumentsInterfaceFactory
     */
    private $shipmentCreationArgumentsFactory;
    /**
     * @var \Magento\Sales\Api\Data\ShipmentCreationArgumentsExtensionInterfaceFactory
     */
    private $shipmentCreationArgumentsExtensionFactory;

    public function __construct(
        \Eguana\GWLogistics\Model\Request\QueryLogisticsInfo $queryTransactionInfo,
        \Eguana\GWLogistics\Model\Request\CvsCreateShipmentOrder $cvsCreateShipmentOrder,
        \Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory $shipmentItemCreationFactory,
        \Magento\Sales\Api\Data\ShipmentTrackCreationInterfaceFactory $shipmentTrackCreationFactory,
        \Magento\Sales\Api\Data\ShipmentCreationArgumentsInterfaceFactory $shipmentCreationArgumentsFactory,
        \Magento\Sales\Api\Data\ShipmentCreationArgumentsExtensionInterfaceFactory $shipmentCreationArgumentsExtensionFactory,
        \Magento\Sales\Model\Order\Email\Container\ShipmentIdentity $shipmentIdentity,
        \Magento\Sales\Api\ShipOrderInterface $shipOrder,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->shipmentItemCreationFactory = $shipmentItemCreationFactory;
        $this->shipmentIdentity = $shipmentIdentity;
        $this->shipOrder = $shipOrder;
        $this->logger = $logger;
        $this->cvsCreateShipmentOrder = $cvsCreateShipmentOrder;
        $this->queryTransactionInfo = $queryTransactionInfo;
        $this->shipmentTrackCreationFactory = $shipmentTrackCreationFactory;
        $this->shipmentCreationArgumentsFactory = $shipmentCreationArgumentsFactory;
        $this->shipmentCreationArgumentsExtensionFactory = $shipmentCreationArgumentsExtensionFactory;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function process($order)
    {
        // do shipment order create
        // request tracking
        // create shipment
        $allPayLogisticsID = $this->createShipmentOrder($order);
        if ($allPayLogisticsID) {
            $this->allPayLogisticsID = $allPayLogisticsID;
            $shipmentNo = $this->requestTrackingInfo($allPayLogisticsID);
        }
        if ($shipmentNo) {
            $this->shipmentNo = $shipmentNo;
            $this->createShipment($order);
        }
    }



    private function createShipmentOrder($order) {
        //maybe need to save all transaction data in a separate table??
        $result = $this->cvsCreateShipmentOrder->execute($order);
        if (isset($result['ResCode']) &&  $result['ResCode'] === '1' && isset($result['AllPayLogisticsID'])) {
            return $result['AllPayLogisticsID'];
        }
        return false;
    }

    private function requestTrackingInfo($allPayLogisticsID) {
        $result = $this->queryTransactionInfo->execute($allPayLogisticsID);
        if (isset($result['ShipmentNo'])) {
            return $result['ShipmentNo'];
        }
        return false;
    }

    private function createShipment($order) {
        /** @var \Magento\Sales\Api\Data\ShipmentItemCreationInterface[] $shipmentItems */
        $shipmentItems = $this->buildShipmentItems($order->getItems());
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
    private function buildShipmentItems($items) {
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
        //maybe use here to save return shipment??
        $arguments = $this->shipmentCreationArgumentsFactory->create();
        $arguments->setExtensionAttributes($extensionAttributes);
        return $arguments;
    }

}
