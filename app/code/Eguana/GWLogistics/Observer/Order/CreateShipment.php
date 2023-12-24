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
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * CreateShipment constructor.
     * @param \Eguana\GWLogistics\Model\Service\CreateShipment $createShipment
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Eguana\GWLogistics\Model\Gateway\Command\CreateShipmentCommand $createShipmentCommand,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {

        $this->logger = $logger;
        $this->createShipmentCommand = $createShipmentCommand;
        $this->orderFactory = $orderFactory;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $observer->getEvent()->getData('invoice');
        /** @var \Magento\Sales\Model\Order $order */
        $order = $invoice->getOrder();

        $this->logger->info('gwlogistics | event sales_order_invoice_save_after fired: order id ', [$order->getId()]);
        if ($order->getShippingMethod() == 'gwlogistics_CVS') {
            try {
                if (!$this->orderHasShipments($order)) {
                    $this->createShipmentCommand->execute($order);
                    $this->logger->info('gwlogistics | created shipment for order id: ', [$order->getIncrementId()]);
                }
            } catch (\Exception $e) {
                $this->logger->error('gwlogistics | ' . $e->getMessage());
            }
        }
    }

    /**
     * @param $order
     * @return bool
     */
    protected function orderHasShipments($order):bool {
        //we will reload order to avoid load missing data
        return $this->orderFactory->create()->load($order->getId())->hasShipments();
    }

}
