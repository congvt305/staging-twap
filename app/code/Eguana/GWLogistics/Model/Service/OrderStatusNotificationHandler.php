<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/18/20
 * Time: 6:19 AM
 */

namespace Eguana\GWLogistics\Model\Service;


use Magento\Framework\Exception\CouldNotSaveException;

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

    public function __construct(
        \Eguana\GWLogistics\Helper\Data $dataHelper,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\Data\ShipmentCommentInterfaceFactory $shipmentCommentInterfaceFactory,
        \Magento\Sales\Api\ShipmentCommentRepositoryInterface $commentRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->dataHelper = $dataHelper;
        $this->shipmentRepository = $shipmentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->shipmentCommentInterfaceFactory = $shipmentCommentInterfaceFactory;
        $this->commentRepository = $commentRepository;
        $this->logger = $logger;
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
        if (isset($notificationData['CheckMacValue'])) {
            try {
                $validated = $this->dataHelper->validateCheckMackValue($notificationData['CheckMacValue']);
                if ($validated) {
                    return false;
                }
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                return false;
            }
        }

        if (isset($notificationData['RtnMsg'], $notificationData['RtnCode'], $notificationData['UpdateStatusDate'], $notificationData['AllPayLogisticsID'])) {
            try {
                $shipment = $this->findShipment($notificationData['AllPayLogisticsID']);
                if ($shipment->getEntityId()) {
                    /** @var \Magento\Sales\Api\Data\ShipmentCommentInterface $shipmentComment */
                    $shipmentComment = $this->shipmentCommentInterfaceFactory->create();
                    $shipmentComment->setParentId($shipment->getEntityId());
                    $shipmentComment->setIsVisibleOnFront(1);
                    $shipmentComment->setComment($this->makeComments($notificationData['RtnMsg'], $notificationData['RtnCode'], $notificationData['UpdateStatusDate']));
                    $this->commentRepository->save($shipmentComment);
                    return true;
                }
            } catch (CouldNotSaveException $e) {
                $this->logger->critical($e->getMessage());
                return false;
            }
        }
    }

    private function findShipment(string $allPayLogisticsID): \Magento\Sales\Api\Data\ShipmentInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('all_pay_logistics_id', $allPayLogisticsID)
            ->create();
        $shipments = $this->shipmentRepository
            ->getList($searchCriteria)
            ->getItems();
        return reset($shipments);

    }
    private function makeComments($message, $code, $date)
    {
        return $message . '|' . $code . '|' . $date . '|' . __('Notified by Green World Logistics.');
    }
}
