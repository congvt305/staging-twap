<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/18/20
 * Time: 6:19 AM
 */

namespace Eguana\GWLogistics\Model\Service;


use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderStatusNotificationHandler
{
    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    private $dataHelper;
    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    private $shipmentRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Magento\Sales\Api\Data\ShipmentCommentInterfaceFactory
     */
    private $shipmentCommentInterfaceFactory;
    /**
     * @var \Magento\Sales\Api\ShipmentCommentRepositoryInterface
     */
    private $commentRepository;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Eguana\GWLogistics\Api\Data\StatusNotificationInterfaceFactory
     */
    private $statusNotificationInterfaceFactory;
    /**
     * @var \Eguana\GWLogistics\Api\StatusNotificationRepositoryInterface
     */
    private $statusNotificationRepository;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var OrderStatusHistoryInterfaceFactory
     */
    private $orderStatusHistoryInterfaceFactory;

    public function __construct(
        \Eguana\GWLogistics\Helper\Data $dataHelper,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\Data\ShipmentCommentInterfaceFactory $shipmentCommentInterfaceFactory,
        \Magento\Sales\Api\ShipmentCommentRepositoryInterface $commentRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory $orderStatusHistoryInterfaceFactory,
        \Eguana\GWLogistics\Api\Data\StatusNotificationInterfaceFactory $statusNotificationInterfaceFactory,
        \Eguana\GWLogistics\Api\StatusNotificationRepositoryInterface $statusNotificationRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->dataHelper = $dataHelper;
        $this->shipmentRepository = $shipmentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->shipmentCommentInterfaceFactory = $shipmentCommentInterfaceFactory;
        $this->commentRepository = $commentRepository;
        $this->logger = $logger;
        $this->statusNotificationInterfaceFactory = $statusNotificationInterfaceFactory;
        $this->statusNotificationRepository = $statusNotificationRepository;
        $this->orderRepository = $orderRepository;
        $this->orderStatusHistoryInterfaceFactory = $orderStatusHistoryInterfaceFactory;
    }

    /*
     * $notificationData:  {
    "MerchantID":"2000132",
    "MerchantTradeNo":"1592515364346",
    "RtnCode":"UNIMART",
    "RtnMsg":"UNIMART",
    "AllPayLogisticsID":"UNIMART",
    "LogisticsType":"UNIMART",
    "LogisticsSubType":"UNIMART",
    "GoodsAmount":"UNIMART",
    "UpdateStatusDate":"UNIMART",
    "ReceiverName":"UNIMART",
    "ReceiverPhone":"991182",
    "ReceiverCellPhone":"馥樺門市",
    "ReceiverEmail":"台北市南港區三重路23號1樓",
    "ReceiverAddress":"",
    "CVSPaymentNo":"0",
    "CVSValidationNo":""
    "BookingNote":""
    "CheckMacValue":""
    }
    */
    public function process(array $notificationData)
    {
        $this->logger->info('gwlogistics | notification for order', $notificationData);
        if (!$this->dataHelper->validateCheckMackValue($notificationData)) {
//            throw new \Exception(__('CheckMacValue is not valid'));
        }
        if (isset($notificationData['RtnMsg'], $notificationData['RtnCode'], $notificationData['UpdateStatusDate'], $notificationData['AllPayLogisticsID'])) {
            try {
                $shipment = $this->findShipment($notificationData['AllPayLogisticsID']);
                if ($shipment && $shipment->getEntityId()) {
                    /** @var \Magento\Sales\Api\Data\ShipmentCommentInterface $shipmentComment */
                    $shipmentComment = $this->shipmentCommentInterfaceFactory->create();
                    $shipmentComment->setParentId($shipment->getEntityId());
                    $shipmentComment->setIsVisibleOnFront(1);
                    $shipmentComment->setComment($this->makeComments($notificationData['RtnMsg'], $notificationData['RtnCode'], $notificationData['UpdateStatusDate']));
                    $this->commentRepository->save($shipmentComment);

                    /** @var \Eguana\GWLogistics\Api\Data\StatusNotificationInterface $statusNotification */
                    $statusNotification = $this->statusNotificationInterfaceFactory->create();
                    $statusNotification->setOrderId($shipment->getOrderId());
                    $statusNotification->setMerchantId($notificationData['MerchantID']);
                    $statusNotification->setRtnCode($notificationData['RtnCode']);
                    $statusNotification->setRtnMsg($notificationData['RtnMsg']);
                    $statusNotification->setAllPayLogisticsId($notificationData['AllPayLogisticsID']);
                    $statusNotification->setMerchantTradeNo($notificationData['MerchantTradeNo']);
                    $statusNotification->setLogisticsType($notificationData['LogisticsType']);
                    $statusNotification->setLogisticsSubType($notificationData['LogisticsSubType']);
                    $statusNotification->setGoodsAmount($notificationData['GoodsAmount']);
                    $statusNotification->setUpdateStatusDate($notificationData['UpdateStatusDate']);
                    $statusNotification->setReceiverName($notificationData['ReceiverName']);
                    $statusNotification->setReceiverPhone($notificationData['ReceiverPhone']);
                    $statusNotification->setReceiverCellPhone($notificationData['ReceiverCellPhone']);
                    $statusNotification->setReceiverEmail($notificationData['ReceiverEmail']);
                    $statusNotification->setReceiverAddress($notificationData['ReceiverAddress']);
                    $this->statusNotificationRepository->save($statusNotification);

                    if ($this->isFailure($statusNotification)) {
                        $orderId = $shipment->getOrderId();
                        $this->failOrder($orderId);
                    }

                    //check status and change order status if needed.
                    if ($this->isCompleted($statusNotification)) {
                        $orderId = $shipment->getOrderId();
                        $this->completeOrder($orderId);
                    }

                    return true;
                }
            } catch (CouldNotSaveException $e) {
                $this->logger->critical($e->getMessage());
                return false;
            }
        }
    }

    private function findShipment(string $allPayLogisticsID)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('all_pay_logistics_id', $allPayLogisticsID)
            ->create();
        $shipments = $this->shipmentRepository
            ->getList($searchCriteria)
            ->getItems();
        return is_array($shipments) ? reset($shipments) : false;

    }
    private function makeComments($message, $code, $date)
    {
        return $message . '|' . $code . '|' . $date . '|' . __('Notified by Green World Logistics.');
    }

    private function completeOrder($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        $statusHistory = $this->orderStatusHistoryInterfaceFactory->create();
        $statusHistory->setStatus('complete');
        $statusHistory->setEntityName('order');
        $statusHistory->setParentId($orderId);
        $statusHistory->setIsVisibleOnFront(1);
        $statusHistory->setComment(__('Customer picked up the shipment in  GWLogistics Cvs Store.'));

        $order->setStatusHistories([$statusHistory]);
        $order->setState(\Magento\Sales\Model\Order::STATE_COMPLETE);
        $order->setStatus('complete');
        $this->orderRepository->save($order);
    }

    /**
     * @param \Eguana\GWLogistics\Api\Data\StatusNotificationInterface $statusNotification
     */
    private function isCompleted($statusNotification) {
        return ($statusNotification->getLogisticsSubType() === 'FAMI'
                && $statusNotification->getRtnCode() === '3022')
            || ($statusNotification->getLogisticsSubType() === 'UNIMART'
                && $statusNotification->getRtnCode() === '2067');
    }

    /**
     * @param \Eguana\GWLogistics\Api\Data\StatusNotificationInterface $statusNotification
     */
    private function isFailure($statusNotification) {
        $FamiFailureCode = '5009';
        $UnimartFalureCodes = [
            '2060', '2028', '2050', '2016', '2049', '2043', '2027'
        ];

        return ($statusNotification->getLogisticsSubType() === 'FAMI'
                && $statusNotification->getRtnCode() === $FamiFailureCode)
            || ($statusNotification->getLogisticsSubType() === 'UNIMART'
                && in_array($statusNotification->getRtnCode(), $UnimartFalureCodes));

    }

    private function failOrder($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        $statusHistory = $this->orderStatusHistoryInterfaceFactory->create();
        $statusHistory->setStatus('complete');
        $statusHistory->setEntityName('order');
        $statusHistory->setParentId($orderId);
        $statusHistory->setIsVisibleOnFront(1);
        $statusHistory->setComment(__('Due to the abnormal delivery of the CVS, please contact the customer service and place the order again.'));

        $order->setStatusHistories([$statusHistory]);
        $order->setState(\Magento\Sales\Model\Order::STATE_COMPLETE);
        $order->setStatus('failure');
        $this->orderRepository->save($order);

    }



}
