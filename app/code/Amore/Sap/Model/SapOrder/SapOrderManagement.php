<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-12
 * Time: 오후 1:25
 */

namespace Amore\Sap\Model\SapOrder;

use Amore\Sap\Api\SapOrderManagementInterface;
use Amore\Sap\Logger\Logger;
use Amore\Sap\Model\Source\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipOrderInterface;
use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Ecpay\Ecpaypayment\Model\Payment;

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
     * @var Payment
     */
    private $ecpayPayment;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

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
     * @param Payment $ecpayPayment
     * @param Logger $logger
     * @param Config $config
     * @param OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
        ShipmentItemCreationInterfaceFactory $shipmentItemCreationInterfaceFactory,
        ShipmentTrackCreationInterfaceFactory $shipmentTrackCreationInterfaceFactory,
        ShipOrderInterface $shipOrderInterface,
        ShipmentIdentity $shipmentIdentity,
        OrderInterfaceFactory $orderInterfaceFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Json $json,
        Payment $ecpayPayment,
        Logger $logger,
        Config $config,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->shipmentItemCreationInterfaceFactory = $shipmentItemCreationInterfaceFactory;
        $this->shipmentTrackCreationInterfaceFactory = $shipmentTrackCreationInterfaceFactory;
        $this->shipOrderInterface = $shipOrderInterface;
        $this->shipmentIdentity = $shipmentIdentity;
        $this->orderInterfaceFactory = $orderInterfaceFactory;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->json = $json;
        $this->ecpayPayment = $ecpayPayment;
        $this->logger = $logger;
        $this->config = $config;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function orderStatus($orderStatusData)
    {
        $result = [];

        $parameters = [
            $orderStatusData['source'],
            $orderStatusData['odrno'],
            $orderStatusData['odrstat'],
            $orderStatusData['ztrackId'],
            $orderStatusData['ugcod'],
            $orderStatusData['ugtxt'],
            $orderStatusData['mallId']
        ];

        if ($this->config->getLoggingCheck()) {
            $this->logger->info('ORDER STATUS REQUEST DATA');
            $this->logger->info($this->json->serialize($parameters));
        }

        /** @var \Magento\Sales\Model\Order $order */
        $orders = $this->getOrderByIncrementId($orderStatusData['odrno']);

        // case that matching order does not exist
        if ($orders->getTotalCount() == 0) {
            $message = "Such Order Increment Id does not Exist.";
            $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
            return $result;
        }

        // case that there are orders with same increment Id
        if ($orders->getTotalCount() > 1) {
            $message = "There are more than two orders with same Increment Id.";
            $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
            return $result;
        }

        // case that order created error in SAP
        // 가용재고 부족 등 상태로 왔을 때 주문 취소할지 아니면 관리자에게 알릴지 다른 방법 찾아야 함
        // 아모레쪽이랑 어떻게 처리할지 얘기 필요
        if ($orderStatusData['odrstat'] == 2) {
            $message = "Order Status Error. Please Check order status.";
            $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
            return $result;
        }

        // case that DN is created
        if ($orderStatusData['odrstat'] == 3) {
            $order = $this->getOrderFromList($orderStatusData['odrno']);
            try {
                if ($order->getStatus() == "sap_processing") {
                    $order->setStatus('preparing');
                    $this->orderRepository->save($order);
                    $message = "Order status changed to preparing successfully.";
                    $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0000");
                } else {
                    $message = "Order status is not SAP Processing. Please check the order.";
                    $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
                }
            } catch (\Exception $exception) {
                $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $exception->getMessage(), "0001");
            }
        } elseif ($orderStatusData['odrstat'] == 4) {
            // ecpay invoice creation
            $order = $this->getOrderFromList($orderStatusData['odrno']);
            $trackingNo = $orderStatusData['ztrackId'];

            if ($order->getStatus() == 'preparing') {
                $shipmentCheck = $order->hasShipments();

                if (!$shipmentCheck) {
                    try {
                        $shipmentId = $this->createShipment($order, $trackingNo);
                    } catch (\Exception $exception) {
                        return $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $exception->getMessage(), "0001");
                    }

                    // case that failed to creat shipment in Magento
                    if (empty($shipmentId)) {
                        $message = "Could not create shipment.";
                        $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
                        // case to create shipment successfully
                    } else {
                        try {
                            $this->setQtyShipToOrderItem($order);
                            $order->setStatus('shipment_processing');
                            $this->orderRepository->save($order);

                            $message = "Shipment Created Successfully.";
                            $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0000");

                            if ($this->config->getEInvoiceActiveCheck('store', $order->getStoreId())) {
                                $ecpayInvoiceResult = $this->ecpayPayment->createEInvoice($order->getEntityId(), $order->getStoreId());
                                $result[$orderStatusData['odrno']]['ecpay'] = $this->validateEInvoiceResult($orderStatusData, $ecpayInvoiceResult);
                                if ($this->config->getLoggingCheck()) {
                                    $this->logger->info('EINVOICE ISSUE RESULT');
                                    $this->logger->info($this->json->serialize($ecpayInvoiceResult));
                                }
                            }
                        } catch (\Exception $exception) {
                            $message = "Something went wrong while saving preparing order : " . $orderStatusData['odrno'];
                            $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
                            $result[$orderStatusData['odrno']]['ecpay'] = ['code' => '0001', 'message' => "Could not create EInvoice. " . $exception->getMessage()];
                        }
                    }
                } else {
                    if ($order->getStatus() != 'shipment_processing') {
                        try {
                            $this->setQtyShipToOrderItem($order);

                            if ($order->getStatus() == 'complete') {
                                $order->setStatus('shipment_processing');
                                $this->orderRepository->save($order);
                            } else {
                                $order->setState('complete');
                                $order->setStatus('shipment_processing');
                                $this->orderRepository->save($order);
                            }

                            $message = "Order already has a shipment";
                            $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");

                            if ($this->config->getEInvoiceActiveCheck('store', $order->getStoreId())) {
                                $ecpayInvoiceResult = $this->ecpayPayment->createEInvoice($order->getEntityId(), $order->getStoreId());
                                $result[$orderStatusData['odrno']]['ecpay'] = $this->validateEInvoiceResult($orderStatusData, $ecpayInvoiceResult);
                            }
                        } catch (\Exception $exception) {
                            $message = "Something went wrong while saving order : " . $orderStatusData['odrno'];
                            $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
                            if ($this->config->getEInvoiceActiveCheck('store', $order->getStoreId())) {
                                $result[$orderStatusData['odrno']]['ecpay'] = ['code' => '0001', 'message' => "Could not create EInvoice. " . $exception->getMessage()];
                            }
                        }
                    } else {
                        $message = "Order already has a shipment and Order Status is already Shipment Processing.";
                        $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
                    }
                }
            } else {
                $message = "Order Status is not Preparing.";
                $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
            }
        } elseif ($orderStatusData['odrstat'] == 9) {
            $message = "Other order status return.";
            $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0000");
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

    /**
     * @param $order \Magento\Sales\Model\Order
     */
    public function setQtyShipToOrderItem($order)
    {
        if ($order->hasInvoices()) {
            $orderItems = $order->getAllItems();
            foreach ($orderItems as $item) {
                if (empty($item->getQtyToShip()) || $item->getIsVirtual()) {
                    continue;
                }
                $item->setQtyShipped($item->getQtyInvoiced());
                $this->orderItemRepository->save($item);
            }
            $this->orderRepository->save($order);
        }
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

    public function validateEInvoiceResult($orderStatusData, $eInvoiceResult)
    {
        if ($eInvoiceResult['RtnCode'] == 1) {
            $message = "EcPay Invoice has been created successfully.";
            $result = $this->orderResultMsg($orderStatusData, $message, "0000");
        } else {
            $message = "Something went wrong while creating EcPay Invoice.";
            $result = $this->orderResultMsg($orderStatusData, $message, "0001");
        }
        return $result;
    }

    /**
     * @param $order \Magento\Sales\Api\Data\OrderInterface
     * @param $trackingNo string
     * @param $shippingMethod string
     * @return int|null
     */
    public function createShipment($order, $trackingNo, $shippingMethod = "BlackCat", $carrierCode = 'flatrate_flatrate')
    {
        $shipmentItems = $this->createShipmentItem($order);

        if ($shipmentItems == null) {
            return null;
        }

        $track = $this->createTrackNo($trackingNo ,$shippingMethod, $carrierCode);
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

    public function createTrackNo($trackingNo, $shippingMethod, $carrierCode)
    {
        /** @var \Magento\Sales\Model\Order\Shipment\TrackCreation $trackNo */
        $trackNo = $this->shipmentTrackCreationInterfaceFactory->create();
        $trackNo->setCarrierCode($carrierCode);
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
