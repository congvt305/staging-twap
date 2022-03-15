<?php
namespace Eghl\PaymentMethod\Observer;

use Magento\Framework\Event\ObserverInterface;
use Eghl\PaymentMethod\Classes\Logger;

class ProcessShipment implements ObserverInterface
{
    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
		
		$prevStatus = $order->getStatus();
        Logger::init($order->getIncrementId());
		
		$order->setStatus('complete');
		$order->setState('complete');
		$order->addStatusHistoryComment('Shipment Done', false);
		Logger::writeString('Order Status Changed from ['.$prevStatus.'] to [complete]');
		Logger::writeString('Order Comment added as >> Shipment Done');
    }
}