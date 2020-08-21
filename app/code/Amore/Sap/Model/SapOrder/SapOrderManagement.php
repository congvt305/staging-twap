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
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Rma\Api\RmaRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipOrderInterface;
use Magento\Sales\Model\Order\Email\Container\ShipmentIdentity;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Ecpay\Ecpaypayment\Model\Payment;
use Magento\Setup\Exception;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

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
     * @var RmaRepositoryInterface
     */
    private $rmaRepository;
    /**
     * @var \Magento\Rma\Model\ResourceModel\Item\CollectionFactory
     */
    private $rmaItemFactory;
    /**
     * @var \Magento\Rma\Model\Rma\Source\StatusFactory
     */
    private $rmaStatusFactory;
    /**
     * @var \Magento\Rma\Model\Rma\Status\HistoryFactory
     */
    private $historyFactory;
    /**
     * @var \Magento\Sales\Api\ShipmentTrackRepositoryInterface
     */
    private $shipmentTrackRepository;
    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;
    /**
     * @var ManagerInterface
     */
    private $eventManager;

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
     * @param RmaRepositoryInterface $rmaRepository
     * @param \Magento\Rma\Model\ResourceModel\Item\CollectionFactory $rmaItemFactory
     * @param \Magento\Rma\Model\Rma\Source\StatusFactory $rmaStatusFactory
     * @param \Magento\Rma\Model\Rma\Status\HistoryFactory $historyFactory
     * @param \Magento\Sales\Api\ShipmentTrackRepositoryInterface $shipmentTrackRepository
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param ManagerInterface $eventManager
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
        OrderItemRepositoryInterface $orderItemRepository,
        RmaRepositoryInterface $rmaRepository,
        \Magento\Rma\Model\ResourceModel\Item\CollectionFactory $rmaItemFactory,
        \Magento\Rma\Model\Rma\Source\StatusFactory $rmaStatusFactory,
        \Magento\Rma\Model\Rma\Status\HistoryFactory $historyFactory,
        \Magento\Sales\Api\ShipmentTrackRepositoryInterface $shipmentTrackRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        ManagerInterface $eventManager
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
        $this->rmaRepository = $rmaRepository;
        $this->rmaItemFactory = $rmaItemFactory;
        $this->rmaStatusFactory = $rmaStatusFactory;
        $this->historyFactory = $historyFactory;
        $this->shipmentTrackRepository = $shipmentTrackRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->eventManager = $eventManager;
    }

    public function orderStatus($orderStatusData)
    {
        $result = [];

        $parameters = [
            "source" => $orderStatusData['source'],
            "odrno" => $orderStatusData['odrno'],
            "odrstat" => $orderStatusData['odrstat'],
            "ztrackId" => $orderStatusData['ztrackId'],
            "ugcod" => $orderStatusData['ugcod'],
            "ugtxt" => $orderStatusData['ugtxt'],
            "mallId" => $orderStatusData['mallId']
        ];

        if ($this->config->getLoggingCheck()) {
            $this->logger->info('ORDER STATUS REQUEST DATA');
            $this->logger->info($this->json->serialize($parameters));
        }

        $incrementId = $orderStatusData['odrno'];

//        /** @var \Magento\Sales\Model\Order $order */
//        $orders = $this->getOrderByIncrementId($incrementId);

        // case that matching order does not exist
//        if ($orders->getTotalCount() == 0) {
//            $message = "Such Order Increment Id does not Exist.";
//            $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
//            return $result;
//        }

        // case that there are orders with same increment Id
//        if ($orders->getTotalCount() > 1) {
//            $message = "There are more than two orders with same Increment Id.";
//            $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
//            return $result;
//        }

        if ($orderStatusData['odrstat'] == 1) {
            if (strpos($incrementId, "R") !== false) {
                $message = "Order Return Status Success.";
                $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0000");

                $rmaIncrementId = str_replace("R", "", $incrementId);
                /** @var \Magento\Rma\Model\Rma $rma */
                $rma = $this->getRma($rmaIncrementId);

                $rma->setData('sap_return_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_SUCCESS);
                $rma->setData('sap_response', $orderStatusData['ugtxt']);
                $this->rmaRepository->save($rma);
            } else {
                $order = $this->getOrderFromList($incrementId);

                $message = "Order Status Success.";
                $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0000");

                $order->setStatus('sap_success');
                $order->setState('processing');
                $order->setData('sap_response', $orderStatusData['ugtxt']);
                $order->setData('sap_order_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_SUCCESS);
                $this->orderRepository->save($order);
            }
            $this->eventManager->dispatch(
                "eguana_bizconnect_operation_processed",
                [
                    'topic_name' => 'amore.sap.order.status.success',
                    'direction' => 'incoming',
                    'to' => "Magento",
                    'serialized_data' => $this->json->serialize($parameters),
                    'status' => 1,
                    'result_message' => $this->json->serialize($result)
                ]
            );

            return $result;
        }

        // case that order created error in SAP
        if ($orderStatusData['odrstat'] == 2) {
            if (strpos($incrementId, "R") !== false) {
                $message = "Order Return Does not created in SAP.";
                $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0000");

                $rmaIncrementId = str_replace("R", "", $incrementId);
                /** @var \Magento\Rma\Model\Rma $rma */
                $rma = $this->getRma($rmaIncrementId);
                $returnSendCheck = $rma->getData('sap_return_send_check');

                if ($returnSendCheck != 2) {
                    $rma->setData('sap_return_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_FAIL);
                    $rma->setData('sap_response', $orderStatusData['ugtxt']);
                    $this->rmaRepository->save($rma);
                }
            } else {
                $message = "Order Does not created in SAP.";
                $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0000");

                $order = $this->getOrderFromList($incrementId);

                $order->setStatus('sap_fail');
                $order->setState('processing');
                $order->setData('sap_response', $orderStatusData['ugtxt']);
                $order->setData('sap_order_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_FAIL);
                $this->orderRepository->save($order);
            }
            $this->eventManager->dispatch(
                "eguana_bizconnect_operation_processed",
                [
                    'topic_name' => 'amore.sap.order.status.fail',
                    'direction' => 'incoming',
                    'to' => "Magento",
                    'serialized_data' => $this->json->serialize($parameters),
                    'status' => 0,
                    'result_message' => $this->json->serialize($result)
                ]
            );
            return $result;
        }

        // case that DN is created
        if ($orderStatusData['odrstat'] == 3) {
            if (strpos($incrementId, "R") !== false) {
                $rmaIncrementId = str_replace("R", "", $incrementId);
                /** @var \Magento\Rma\Model\Rma $rma */
                $rma = $this->getRma($rmaIncrementId);

                $rma->setData('sap_return_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_SUCCESS);
                $rma->setData('sap_response', $orderStatusData['ugtxt']);
                $this->rmaRepository->save($rma);

                $message = "Get DN Info from SAP for Return Order.";
                $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0000");
            } else {
                $order = $this->getOrderFromList($incrementId);
                try {
                    $order->setStatus('preparing');
                    $order->setState('processing');
                    $order->setData('sap_order_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_SUCCESS);
                    $order->setData('sap_response', $orderStatusData['ugtxt']);
                    $this->orderRepository->save($order);
                    $message = "Order status changed to preparing successfully.";
                    $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0000");
                } catch (\Exception $exception) {
                    $order->setData('sap_response', $exception->getMessage());
                    $this->orderRepository->save($order);
                    $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $exception->getMessage(), "0001");
                }
            }
            $this->eventManager->dispatch(
                "eguana_bizconnect_operation_processed",
                [
                    'topic_name' => 'amore.sap.order.status.dn',
                    'direction' => 'incoming',
                    'to' => "Magento",
                    'serialized_data' => $this->json->serialize($parameters),
                    'status' => $result[$orderStatusData['odrno']]['code'] == "0000" ? 1 : 0,
                    'result_message' => $this->json->serialize($result)
                ]
            );
            return $result;
        }

        if ($orderStatusData['odrstat'] == 4) {
            // ecpay invoice creation
            $trackingNo = $orderStatusData['ztrackId'];

            if (strpos($incrementId, "R") !== false) {
                $rmaIncrementId = str_replace("R", "", $incrementId);
                /** @var \Magento\Rma\Model\Rma $rma */
                $rma = $this->getRma($rmaIncrementId);

                if ($rma->getStatus() == 'authorized') {
                    try {
                        $this->rmaChangeToReceived($rma);
                        $message = "Rma " . $orderStatusData['odrno'] . " status changed to approved Successfully.";
                        $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0000");
                        $rma->setStatus('processed_closed');
                        $rma->setData('sap_return_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_SUCCESS);
                        $rma->setData('sap_response', $orderStatusData['ugtxt']);
                        $this->rmaRepository->save($rma);
                    } catch (LocalizedException $exception) {
                        $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $exception->getMessage(), "0001");
                    } catch (\Exception $exception) {
                        $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $exception->getMessage(), "0001");
                    }
                } else {
                    $message = "RMA Status is not Authorized.";
                    $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
                }
            } else {
                $order = $this->getOrderFromList($incrementId);
                if ($order->getStatus() == 'preparing' || $order->getStatus() == 'sap_processing' || $order->getStatus() == 'processing_with_shipment'
                    || $order->getStatus() == 'sap_success' || $order->getStatus() == 'sap_fail') {
                    $shipmentCheck = $order->hasShipments();

                    if (!$shipmentCheck) {
                        if ($order->getShippingMethod() == "blackcat_homedelivery") {
                            try {
                                $shipmentId = $this->createShipment($order, $trackingNo);
                            } catch (\Exception $exception) {
                                $order->setData('sap_response', $exception->getMessage());
                                $this->orderRepository->save($order);
                                $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $exception->getMessage(), "0001");

                                $this->eventManager->dispatch(
                                    "eguana_bizconnect_operation_processed",
                                    [
                                        'topic_name' => 'amore.sap.order.status.gi',
                                        'direction' => 'incoming',
                                        'to' => "Magento",
                                        'serialized_data' => $this->json->serialize($parameters),
                                        'status' => $result[$orderStatusData['odrno']]['code'] == "0000" ? 1 : 0,
                                        'result_message' => $this->json->serialize($result)
                                    ]
                                );

                                return $result;
                            }

                            // case that failed to creat shipment in Magento
                            if (empty($shipmentId)) {
                                $message = "Could not create shipment.";

                                $order->setData('sap_response', $message);
                                $this->orderRepository->save($order);

                                $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
                                // case to create shipment successfully
                            } else {
                                try {
                                    $this->setQtyShipToOrderItem($order);
                                    $order->setStatus('shipment_processing');
                                    $order->setData('sap_response', $orderStatusData['ugtxt']);
                                    $this->orderRepository->save($order);

                                    $message = "Shipment Created Successfully.";
                                    $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0000");

                                } catch (\Exception $exception) {
                                    $message = "Something went wrong while saving item shipped to order : " . $incrementId;
                                    $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
                                }

                                if ($this->config->getEInvoiceActiveCheck('store', $order->getStoreId())) {
                                    try {
                                        $ecpayInvoiceResult = $this->ecpayPayment->createEInvoice($order->getEntityId(), $order->getStoreId());
                                        $result[$orderStatusData['odrno']]['ecpay'] = $this->validateEInvoiceResult($orderStatusData, $ecpayInvoiceResult);
                                        if ($this->config->getLoggingCheck()) {
                                            $this->logger->info('EINVOICE ISSUE RESULT');
                                            $this->logger->info($this->json->serialize($ecpayInvoiceResult));
                                        }
                                    } catch (\Exception $exception) {
                                        $result[$orderStatusData['odrno']]['code'] = "0001";
                                        $result[$orderStatusData['odrno']]['ecpay'] = ['code' => '0001', 'message' => "Could not create EInvoice. " . $exception->getMessage()];
                                    }
                                }
                            }
                        } else {
                            $message = "Shipping method is not BlackCat and Shipment is not Exist. Please Check Order.";
                            $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
                        }
                    } else {
                        if ($order->getShippingMethod() == 'gwlogistics_CVS') {
                            try {
                                /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
                                $track = $this->getSingleTrackByOrder($order);
                                $trackingNumber = $track->getTrackNumber();

                                if ($trackingNumber != $orderStatusData['ztrackId']) {
                                    $trackById = $this->shipmentTrackRepository->get($track->getEntityId());
                                    $trackById->setTrackNumber($orderStatusData['ztrackId']);
                                    $this->shipmentTrackRepository->save($trackById);
                                }

                                $this->setQtyShipToOrderItem($order);

                                if ($order->getStatus() == 'complete') {
                                    $order->setStatus('shipment_processing');
                                    $order->setData('sap_response', $orderStatusData['ugtxt']);
                                    $this->orderRepository->save($order);
                                } else {
                                    $order->setState('complete');
                                    $order->setStatus('shipment_processing');
                                    $order->setData('sap_response', $orderStatusData['ugtxt']);
                                    $this->orderRepository->save($order);
                                }

                                $message = "Order already has a shipment. Order Status changed to Shipment Processing.";
                                $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0000");
                            } catch (\Exception $exception) {
                                $message = "Something went wrong while saving order : " . $incrementId;
                                $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
                            }

                            if ($this->config->getEInvoiceActiveCheck('store', $order->getStoreId())) {
                                try {
                                    $ecpayInvoiceResult = $this->ecpayPayment->createEInvoice($order->getEntityId(), $order->getStoreId());
                                    $result[$orderStatusData['odrno']]['ecpay'] = $this->validateEInvoiceResult($orderStatusData, $ecpayInvoiceResult);
                                } catch (\Exception $exception) {
                                    $result[$orderStatusData['odrno']]['code'] = "0001";
                                    $result[$orderStatusData['odrno']]['ecpay'] = ['code' => '0001', 'message' => "Could not create EInvoice. " . $exception->getMessage()];
                                }
                            }
                        } else {
                            /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
                            $track = $this->getSingleTrackByOrder($order);
                            $trackingNumber = $track->getTrackNumber();

                            if ($trackingNumber != $orderStatusData['ztrackId']) {
                                $trackById = $this->shipmentTrackRepository->get($track->getEntityId());
                                $trackById->setTrackNumber($orderStatusData['ztrackId']);
                                $this->shipmentTrackRepository->save($trackById);

                                $message = "Shipping method is not Greenworld and Trackin number is changed.";
                                $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");


                                $this->eventManager->dispatch(
                                    "eguana_bizconnect_operation_processed",
                                    [
                                        'topic_name' => 'amore.sap.order.status.gi',
                                        'direction' => 'incoming',
                                        'to' => "Magento",
                                        'serialized_data' => $this->json->serialize($parameters),
                                        'status' => $result[$orderStatusData['odrno']]['code'] == "0000" ? 1 : 0,
                                        'result_message' => $this->json->serialize($result)
                                    ]
                                );

                                return $result;
                            }

                            $message = "Shipping method is not Greenworld and Shipment exists. Please Check Order.";
                            $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
                        }
                    }
                } else {
                    $message = "Order Status is not Proper status. Please check order status.";
                    $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
                }
            }
            $this->eventManager->dispatch(
                "eguana_bizconnect_operation_processed",
                [
                    'topic_name' => 'amore.sap.order.status.gi',
                    'direction' => 'incoming',
                    'to' => "Magento",
                    'serialized_data' => $this->json->serialize($parameters),
                    'status' => $result[$orderStatusData['odrno']]['code'] == "0000" ? 1 : 0,
                    'result_message' => $this->json->serialize($result)
                ]
            );

            return $result;
        }

        if ($orderStatusData['odrstat'] == 9) {
            $order = $this->getOrderFromList($incrementId);

            $creditmemo = $this->getCreditmemoByOrder($order->getEntityId());

            if (empty($creditmemo)) {
                $message = "Creditmemo for this order does not exist.";
                $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0001");
            } else {
                $creditmemo->setData('sap_creditmemo_send_check', SapOrderConfirmData::ORDER_SENT_TO_SAP_SUCCESS);
                $this->creditmemoRepository->save($creditmemo);

                $message = "Order Canceled Successfully.";
                $result[$orderStatusData['odrno']] = $this->orderResultMsg($orderStatusData, $message, "0000");
            }

            $this->eventManager->dispatch(
                "eguana_bizconnect_operation_processed",
                [
                    'topic_name' => 'amore.sap.order.status.refund',
                    'direction' => 'incoming',
                    'to' => "Magento",
                    'serialized_data' => $this->json->serialize($parameters),
                    'status' => $result[$orderStatusData['odrno']]['code'] == "0000" ? 1 : 0,
                    'result_message' => $this->json->serialize($result)
                ]
            );

            return $result;
        }

        return $result;
    }

    public function orderResultMsg($request, $message, $code)
    {
        return [
            'code' => $code,
            'message' => $message,
            'data' => [
                'order_status_code' => $request['odrstat'],
                'order_status_txt' => $this->orderStatusList($request['odrstat']),
                'order_error_code' => $request['ugcod'],
                'order_error_txt' => $request['ugtxt'],
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

    public function getCreditmemoByOrder($orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('order_id', $orderId)->create();

        try {
            $creditmemos = $this->creditmemoRepository->getList($searchCriteria)->getItems();
            return reset($creditmemos);
        } catch (\Exception $exception) {
            return null;
        }
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
     * @param string $carrierCode
     * @return int|null
     */
    public function createShipment($order, $trackingNo, $shippingMethod = "BlackCat", $carrierCode = 'blackcat')
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
     * @param $order \Magento\Sales\Model\Order
     */
    public function getSingleTrackByOrder($order)
    {
        $tracks = $order->getTracksCollection();

        return $tracks->getFirstItem();
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

    public function getRma($rmaIncrementId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $rmaIncrementId, 'eq')
            ->create();

        $rma = $this->rmaRepository->getList($searchCriteria)->getItems();
        $rmaCount = $this->rmaRepository->getList($searchCriteria)->getTotalCount();

        if ($rmaCount >= 1) {
            return reset($rma);
        } else {
            return null;
        }
    }

    /**
     * @param $rma \Magento\Rma\Model\Rma
     * @param $order \Magento\Sales\Model\Order
     */
    public function rmaChangeToReceived($rma)
    {
        try {
            /** @var $sourceStatus \Magento\Rma\Model\Rma\Source\Status */
            $sourceStatus = $this->rmaStatusFactory->create();
            $rma->setStatus($sourceStatus->getStatusByItems($this->getRmaItemStatus($rma->getEntityId())))->setIsUpdate(1);

            if (!$rma->saveRma($this->getRmaRequestData($rma))) {
                $this->logger->critical(__("Cron Could not save RMA %1.", $rma->getEntityId()));
            }

            /** @var $statusHistory \Magento\Rma\Model\Rma\Status\History */
            $statusHistory = $this->historyFactory->create();
            $statusHistory->setRmaEntityId($rma->getEntityId());
            if ($rma->getIsSendAuthEmail()) {
                $statusHistory->sendAuthorizeEmail();
            }
            if ($rma->getStatus() !== $rma->getOrigData('status')) {
                $statusHistory->saveSystemComment();
            }
        } catch (LocalizedException $e) {
            $this->logger->critical('Change Status EXCEPTION : ' . $e->getMessage());
            throw new LocalizedException(__('Change Status EXCEPTION : ' . $e->getMessage()));
        } catch (\Exception $e) {
            $this->logger->critical('Change Status EXCEPTION : ' . $e->getMessage());
            throw new Exception(__('Change Status EXCEPTION : ' . $e->getMessage()));
        }
    }

    /**
     * @param \Magento\Rma\Model\Rma $rma
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getRmaRequestData($rma)
    {
        $requestData = [
            'entity_id' => $rma->getEntityId(),
            'title' => '',
            'number' => '',
            'items' => $this->getRmaItemData($rma->getEntityId())
        ];
        return $requestData;
    }

    public function getRmaItemData($rmaId)
    {
        $itemData = [];
        $rmaItemCollection = $this->getRmaItemCollection($rmaId);
        /** @var \Magento\Rma\Model\Item $rmaItem */
        foreach ($rmaItemCollection as $rmaItem) {
            $itemData[$rmaItem->getEntityId()] = [
                'qty_returned' => $rmaItem->getQtyAuthorized(),
                'qty_approved' => $rmaItem->getQtyAuthorized(),
                'status' => 'approved',
                'order_item_id' => $rmaItem->getOrderItemId(),
                'entity_id' => $rmaItem->getEntityId(),
                'resolution' => $rmaItem->getResolution()
            ];
        }
        return $itemData;
    }

    public function getRmaItemCollection($rmaId)
    {
        /** @var \Magento\Rma\Model\ResourceModel\Item\Collection $rmaItemCollection */
        $rmaItemCollection = $this->rmaItemFactory->create();
        $rmaItemCollection->addFieldToFilter('rma_entity_id', $rmaId)
            ->addAttributeToSelect("*");

        return $rmaItemCollection->getItems();
    }

    public function getRmaItemStatus($rmaId)
    {
        $statuses = [];
        $rmaItemCollection = $this->getRmaItemCollection($rmaId);
        /** @var \Magento\Rma\Model\Item $rmaItem */
        foreach ($rmaItemCollection as $rmaItem) {
            $statuses[$rmaItem->getId()] = 'received';
        }
        return $statuses;
    }
}
