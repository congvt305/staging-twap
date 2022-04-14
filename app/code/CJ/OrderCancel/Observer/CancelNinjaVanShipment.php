<?php

namespace CJ\OrderCancel\Observer;

use CJ\NinjaVanShipping\Helper\Data as NinjaVanHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\Manager as MessageManager;
use Magento\Sales\Model\Order;
use CJ\NinjaVanShipping\Model\Request\CancelShipment as NinjaVanCancelShipment;
use CJ\NinjaVanShipping\Logger\Logger as NinjaVanShippingLogger;

class CancelNinjaVanShipment implements ObserverInterface
{
    /**
     * @var NinjaVanHelper
     */
    private NinjaVanHelper $ninjavanHelper;
    /**
     * @var NinjaVanShippingLogger
     */
    private NinjaVanShippingLogger $logger;
    /**
     * @var NinjaVanCancelShipment
     */
    private NinjaVanCancelShipment $ninjavanCancelShipment;
    /**
     * @var MessageManager
     */
    protected MessageManager $messageManager;

    /**
     * @param NinjaVanHelper $ninjavanHelper
     * @param NinjaVanShippingLogger $logger
     * @param NinjaVanCancelShipment $ninjavanCancelShipment
     * @param MessageManager $messageManager
     */
    public function __construct(
        NinjaVanHelper $ninjavanHelper,
        NinjaVanShippingLogger $logger,
        NinjaVanCancelShipment $ninjavanCancelShipment,
        MessageManager $messageManager
    )
    {
        $this->ninjavanHelper = $ninjavanHelper;
        $this->logger = $logger;
        $this->ninjavanCancelShipment = $ninjavanCancelShipment;
        $this->messageManager = $messageManager;
    }


    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ((bool)$this->ninjavanHelper->isNinjaVanEnabled()) {
            /** @var Order $order */
            $order = $observer->getEvent()->getData('order');
            if ($order->getShippingMethod() == 'ninjavan_tablerate' &&
                $trackingNumber = $this->getTrackingNumber($order)
            ) {
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
                } catch (\Exception $exception) {
                    $this->messageManager->addExceptionMessage(
                        $exception,
                        __('Something went wrong while canceling the shipment.')
                    );
                }
            }
        }
    }

    /**
     * @param Order $order
     * @return string
     */
    private function getTrackingNumber(Order $order):string
    {
        $trackingNumber = '';
        /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection $tracking */
        $trackingCollection = $order->getTracksCollection();
        if ($trackingCollection->getSize()) {
            // innis only has 1 shipment per 1 order
            /** @var \Magento\Sales\Model\Order\Shipment\Track $tracking */
            $tracking = $trackingCollection->getFirstItem();
            $trackingNumber = $tracking->getTrackNumber();
        }
        return $trackingNumber;
    }
}
