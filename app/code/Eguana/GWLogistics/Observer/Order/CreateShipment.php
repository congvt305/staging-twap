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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Eguana\GWLogistics\Model\Gateway\Command\CreateShipmentCommand
     */
    private $createShipmentCommand;

    /**
     * CreateShipment constructor.
     * @param \Eguana\GWLogistics\Model\Service\CreateShipment $createShipment
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Eguana\GWLogistics\Model\Gateway\Command\CreateShipmentCommand $createShipmentCommand,
        \Psr\Log\LoggerInterface $logger
    ) {

        $this->logger = $logger;
        $this->createShipmentCommand = $createShipmentCommand;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $observer->getEvent()->getData('invoice');
        /** @var \Magento\Sales\Model\Order $order */
        $order = $invoice->getOrder();
        $this->logger->info('gwlogistics | event sales_order_invoice_pay fired: order id ', [$order->getId()]);
        try {
            $this->createShipmentCommand->execute($order);
        } catch (\Exception $e) {
            $this->logger->error('gwlogistics | ' . $e->getMessage());
        }
    }

}
