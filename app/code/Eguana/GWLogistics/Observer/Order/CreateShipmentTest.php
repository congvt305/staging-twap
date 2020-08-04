<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 8/4/20
 * Time: 11:41 AM
 */

namespace Eguana\GWLogistics\Observer\Order;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CreateShipmentTest implements ObserverInterface
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
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        $this->logger->info('gwlogistics | event checkout_submit_all_after fired: order id ', [$order->getId()]);
    }
}
