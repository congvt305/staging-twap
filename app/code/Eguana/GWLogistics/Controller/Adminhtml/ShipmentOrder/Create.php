<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 8/3/20
 * Time: 8:18 AM
 */

namespace Eguana\GWLogistics\Controller\Adminhtml\ShipmentOrder;

use Magento\Sales\Api\ShipmentRepositoryInterface;

class Create extends \Magento\Backend\App\Action
{
    /**
     * @var \Eguana\GWLogistics\Model\Service\CreateShipment
     */
    private $createShipmentOrder;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackRepository
     */
    private $trackRepository;
    /**
     * @var \Magento\Sales\Api\Data\ShipmentTrackInterfaceFactory
     */
    private $shipmentTrackFactory;
    /**
     * @var string
     */
    private $shipmentNo;
    /**
     * @var \Eguana\GWLogistics\Model\Request\QueryLogisticsInfo
     */
    private $queryLogisticsInfo;
    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;
    /**
     * @var int
     */
    private $shipmentId;

    public function __construct(
        \Eguana\GWLogistics\Model\Request\CvsCreateShipmentOrder $createShipmentOrder,
        \Eguana\GWLogistics\Model\Request\QueryLogisticsInfo $queryLogisticsInfo,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\Data\ShipmentTrackInterfaceFactory $shipmentTrackFactory,
        \Magento\Sales\Model\Order\Shipment\TrackRepository $trackRepository,
        \Magento\Backend\App\Action\Context $context
    )
    {
        parent::__construct($context);
        $this->createShipmentOrder = $createShipmentOrder;
        $this->orderRepository = $orderRepository;
        $this->trackRepository = $trackRepository;
        $this->shipmentTrackFactory = $shipmentTrackFactory;
        $this->queryLogisticsInfo = $queryLogisticsInfo;
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Eguana_GWLogistics::shipment_order_create';

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $this->shipmentId = intval($shipmentId);
        try {
            $shipment = $this->shipmentRepository->get($this->shipmentId);
            $orderId = $shipment->getOrderId();
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get(intval($orderId));
            $result = $this->createShipmentOrder->sendRequest($order);
            if (isset($result['ErrorMessage'])) {
                $this->messageManager->addErrorMessage($result['ErrorMessage']);
                $this->_redirect('adminhtml/order_shipment/view', ['shipment_id' => $shipmentId]);
            }
            if (!isset($result['AllPayLogisticsID'])) {
                $this->messageManager->addErrorMessage(__('Green World Shipment Order creation failed.'));
                $this->_redirect('adminhtml/order_shipment/view', ['shipment_id' => $shipmentId]);
            }
            $shipment->setData('all_pay_logistics_id', $result['AllPayLogisticsID']);
            $this->shipmentRepository->save($shipment);
            //todo comment save even when error message

            $response = $this->queryLogisticsInfo->sendRequest($result['AllPayLogisticsID'], $order->getStoreId());

            if (!isset($response['ShipmentNo'])) {
                $this->messageManager->addErrorMessage(array_key_first($response));
            }
            $this->shipmentNo = $response['ShipmentNo'];
            $this->saveTrack($order, $response['ShipmentNo']);
            $this->messageManager->addSuccessMessage(__('Green World Shipment Order is created. Shipment Number is %1', $response['ShipmentNo']));

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirect('adminhtml/order_shipment/view', ['shipment_id' => $shipmentId]);
        }
        $this->_redirect('adminhtml/order_shipment/view', ['shipment_id' => $shipmentId]);
    }

    private function saveTrack($order, $shipmentNo)
    {
        $track = $this->shipmentTrackFactory->create();
        $track->setTrackNumber($shipmentNo);
        $track->setOrderId($order->getId());
        $track->setParentId($this->shipmentId);
        $track->setTitle('GWLogistics CVS');
        $track->setCarrierCode('gwlogistics');
        $this->trackRepository->save($track);
    }
}
