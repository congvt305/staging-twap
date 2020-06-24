<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-12
 * Time: 오후 1:25
 */

namespace Amore\Sap\Model\SapOrder;

use Amore\Sap\Api\SapOrderManagementInterface;
use Amore\Sap\Model\SapOrder\SapOrderConfirm;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipOrderInterface;
use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;

class SapOrderManagement implements SapOrderManagementInterface
{
    const SAP_ORDER_CREATION = 'Order Created';

    const SAP_ORDER_CREATION_ERROR = 'Order Created Error(SAP)';

    const SAP_ORDER_DELIVERY_CREATION = 'Delivery Created';

    const SAP_ORDER_DELIVERY_START_OR_PRODUCT_RETURNED = 'Goods Issue/Return GR';

    const SAP_ORDER_CANCEL = 'Order Canceled';

    /**
     * @var OrderInterfaceFactory
     */
    private $orderInterfaceFactory;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var Json
     */
    private $json;
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

    private $appendComments = false;

    /**
     * SapOrderManagement constructor.
     * @param ShipmentItemCreationInterfaceFactory $shipmentItemCreationInterfaceFactory
     * @param ShipmentTrackCreationInterfaceFactory $shipmentTrackCreationInterfaceFactory
     * @param ShipOrderInterface $shipOrderInterface
     * @param ShipmentIdentity $shipmentIdentity
     * @param OrderInterfaceFactory $orderInterfaceFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Json $json
     */
    public function __construct(
        ShipmentItemCreationInterfaceFactory $shipmentItemCreationInterfaceFactory,
        ShipmentTrackCreationInterfaceFactory $shipmentTrackCreationInterfaceFactory,
        ShipOrderInterface $shipOrderInterface,
        ShipmentIdentity $shipmentIdentity,
        OrderInterfaceFactory $orderInterfaceFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Json $json
    ) {
        $this->shipmentItemCreationInterfaceFactory = $shipmentItemCreationInterfaceFactory;
        $this->shipmentTrackCreationInterfaceFactory = $shipmentTrackCreationInterfaceFactory;
        $this->shipOrderInterface = $shipOrderInterface;
        $this->shipmentIdentity = $shipmentIdentity;
        $this->orderInterfaceFactory = $orderInterfaceFactory;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->json = $json;
    }

    public function orderStatus($orderStatusData)
    {
        $result = [];

        /** @var \Magento\Sales\Model\Order $order */
        $orders = $this->getOrderByIncrementId($orderStatusData['odrno']);

        // case that matching order does not exist
        if ($orders->getTotalCount() == 0) {
            $message = "Such Order Increment Id does not Exist.";
            $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
        // case that there are orders with same increment Id
        } elseif ($orders->getTotalCount() > 1) {
            $message = "There are more than two orders with same Increment Id.";
            $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
        } else {
            // case that order created error in SAP
            if ($orderStatusData['odrstat'] == 2) {
                $message = "Order Status Error. Please Check order status.";
                $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
            // case that DN is created
            } elseif ($orderStatusData['odrstat'] == 3) {

                $order = $this->getOrderFromList($orderStatusData['odrno']);

                $trackingNo = $orderStatusData['ztrackId'];

                $shipmentCheck = $order->hasShipments();

                if (!$shipmentCheck) {
                    $shipmentId = $this->createShipment($order, $trackingNo);
                    // case that failed to creat shipment in Magento
                    if (empty($shipmentId)) {
                        $message = "Could not create shipment.";
                        $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
                        // case to create shipment successfully
                    } else {
                        $message = "Shipment Created Successfully.";
                        $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0000");

                        $order->setStatus('preparing');
                        $this->orderRepository->save($order);
                    }
                } else {
                    $message = "Order already has a shipment";
                    $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
                }
            // 기타 order status 일 때. 각 status 별 프로세스 확실해지면 제대로 하기.
            } else {
                $message =  "Other order status return.";
                $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
            }
        }
        return $result;
    }

    public function orderResultMsg($request, $message, $code)
    {
        return [
            'code' => $code,
            'message' => $message,
            'data' => [
                'order_status_code' => $request['odrno'],
                'order_status_txt' => $this->orderStatusList($request['odrstat'])
            ]
        ];
    }

    public function getOrderFromList($incrementId)
    {
        $orders = $this->getOrderByIncrementId($incrementId)->getItems();
        return reset($orders);
    }

    /**
     * @param $incrementId
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     */
    public function getOrderByIncrementId($incrementId)
    {
        $orderFilter = $this->searchCriteriaBuilder->addFilter('increment_id', $incrementId)->create();

        return $this->orderRepository->getList($orderFilter);
    }

    public function orderStatusList($orderStatus)
    {
        switch ($orderStatus) {
            case 1:
                $status = self::SAP_ORDER_CREATION;
                break;
            case 2:
                $status = self::SAP_ORDER_CREATION_ERROR;
                break;
            case 3:
                $status = self::SAP_ORDER_DELIVERY_CREATION;
                break;
            case 4:
                $status = self::SAP_ORDER_DELIVERY_START_OR_PRODUCT_RETURNED;
                break;
            case 9:
                $status = self::SAP_ORDER_CANCEL;
                break;
            default:
                $status = self::SAP_ORDER_DELIVERY_CREATION;
        }
        return $status;
    }

    /**
     * @param $order \Magento\Sales\Api\Data\OrderInterface
     * @param $trackingNo string
     * @param $shippingMethod string
     * @return int|null
     */
    public function createShipment($order, $trackingNo, $shippingMethod = "test shipping method")
    {
        $shipmentItems = $this->createShipmentItem($order);

        if ($shipmentItems == null) {
            return null;
        }

        $track = $this->createTrackNo($trackingNo, $shippingMethod);
        $orderEntityId = $order->getEntityId();

        return $this->shipOrderInterface
            ->execute(
                $orderEntityId,
                $shipmentItems,
                $this->shipmentIdentity->isEnabled(),
                $this->appendComments,
                null,
                $track
            );
    }

    public function createTrackNo($trackingNo, $shippingMethod)
    {
        /** @var \Magento\Sales\Model\Order\Shipment\TrackCreation $trackNo */
        $trackNo = $this->shipmentTrackCreationInterfaceFactory->create();
        $trackNo->setCarrierCode('custom');
        $trackNo->setTitle($shippingMethod);
        $trackNo->setTrackNumber($trackingNo);

        return [$trackNo];
    }

    /**
     * @param $order \Magento\Sales\Api\Data\OrderInterface
     * @return array
     */
    public function createShipmentItem($order)
    {
        $shipmentItems = [];

        $orderItems = $order->getAllItems();
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
