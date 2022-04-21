<?php

namespace CJ\NinjaVanShipping\Controller\Adminhtml\Index;

use Amore\Sap\Model\SapOrder\SapOrderConfirmData;
use CJ\NinjaVanShipping\Helper\Data as NinjaVanHelper;
use CJ\NinjaVanShipping\Logger\Logger as NinjaVanShippingLogger;
use CJ\NinjaVanShipping\Model\Request\CancelShipment as NinjaVanCancelShipment;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection;
use Magento\Framework\Controller\ResultFactory;

class CancelShipment extends Action
{
    /**
     * @var NinjaVanHelper
     */
    protected $ninjavanHelper;
    /**
     * @var NinjaVanShippingLogger
     */
    protected $logger;
    /**
     * @var NinjaVanCancelShipment
     */
    protected $ninjavanCancelShipment;
    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @param Context $context
     * @param NinjaVanHelper $ninjavanHelper
     * @param NinjaVanShippingLogger $logger
     * @param NinjaVanCancelShipment $ninjavanCancelShipment
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Context                $context,
        NinjaVanHelper         $ninjavanHelper,
        NinjaVanShippingLogger $logger,
        NinjaVanCancelShipment $ninjavanCancelShipment,
        OrderFactory           $orderFactory
    )
    {
        parent::__construct($context);
        $this->ninjavanHelper = $ninjavanHelper;
        $this->logger = $logger;
        $this->ninjavanCancelShipment = $ninjavanCancelShipment;
        $this->orderFactory = $orderFactory;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderFactory->create()->load($orderId);

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        if (in_array($order->getData('sap_order_send_check'), [SapOrderConfirmData::ORDER_RESENT_TO_SAP_SUCCESS, SapOrderConfirmData::ORDER_SENT_TO_SAP_SUCCESS])){
            $this->messageManager->addErrorMessage(__('This order cannot cancel now.. plz cancel sap order first and then credit memo for refund and cancel ninjavan shipment'));
            return $resultRedirect;
        }

        if ((bool)$this->ninjavanHelper->isNinjaVanEnabled() && $order->getId()) {


            if ($order->getShippingMethod() == 'ninjavan_tablerate' && $trackingNumber = $this->getTrackingNumber($order)) {
                try {
                    $response = $this->ninjavanCancelShipment->requestCancelShipment($trackingNumber, $order);
                    $message = 'The NinjaVan Shipment successfully cancelled';
                    if (isset($response['trackingId'])) {
                        $message .= ' - Tracking Id: ' . $response['trackingId'];
                    }
                    if (isset($response['status'])) {
                        $message .= ' - Status: ' . $response['status'];
                    }
                    if (isset($response['updatedAt'])) {
                        $message .= ' - Updated at: ' . $response['updatedAt'];
                    }
                    $this->messageManager->addSuccessMessage($message);
                    $this->logger->info($message);
                    $order->addCommentToStatusHistory($message);
                    $order->setData('ninjavan_shipment_cancel', 1);
                    $order->save();
                } catch (Exception $exception) {
                    $this->messageManager->addExceptionMessage(
                        $exception,
                        __('Something went wrong while canceling the shipment.')
                    );
                }
            }
        }

        return $resultRedirect;
    }

    /**
     * @param Order $order
     * @return string
     */
    private function getTrackingNumber(Order $order)
    {
        $trackingNumber = '';
        /** @var Collection $trackingCollection */
        $trackingCollection = $order->getTracksCollection();
        if ($trackingCollection->getSize()) {
            // innis only has 1 shipment per 1 order
            /** @var Track $tracking */
            $tracking = $trackingCollection->getLastItem();
            $trackingNumber = $tracking->getTrackNumber();
        }
        return $trackingNumber;
    }
}
