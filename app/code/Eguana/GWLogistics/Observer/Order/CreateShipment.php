<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/19/20
 * Time: 10:27 AM
 */

namespace Eguana\GWLogistics\Observer\Order;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CreateShipment implements ObserverInterface
{
    /**
     * @var \Eguana\GWLogistics\Model\Service\CreateShipment
     */
    private $createShipment;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * CreateShipment constructor.
     * @param \Eguana\GWLogistics\Model\Service\CreateShipment $createShipment
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Eguana\GWLogistics\Model\Service\CreateShipment $createShipment,
        \Psr\Log\LoggerInterface $logger
    ) {

        $this->createShipment = $createShipment;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $observer->getEvent()->getData('invoice');
        /** @var \Magento\Sales\Model\Order $order */
        $order = $invoice->getOrder();

        //create shipment here
        $state = $invoice->getState();
        $this->logger->info('gwlogistics | start creating shipment: invoice state ', [$state]);
        if ($state !== 2 || $order->getShippingMethod() !== 'gwlogistics_CVS') {
            $this->logger->info('gwlogistics | start creating shipment: return state ');
            return;
        }

        try {
            $this->logger->info('gwlogistics | start creating shipment: order id ', [$order->getId()]);
            $this->createShipment->process($order);
        } catch (\Exception $e) {
            $this->logger->critical('gwlogistics | start creating shipment failed: order id ', [$order->getId()]);
            $this->logger->error($e->getMessage());
        }
    }

}
