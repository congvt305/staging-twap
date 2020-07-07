<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created byPhpStorm
 * User:  Abbas
 * Date: 07/01/20
 * Time: 10:30 am
 */

namespace Eguana\OrderDeliveryMessage\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Sales\Model\OrderRepository;
use Psr\Log\LoggerInterface;
use Magento\Framework\Escaper;

/**
 * Save Delivery Message after place order
 */
class SaveMessage implements ObserverInterface
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * SaveMessage constructor.
     * @param DataPersistorInterface $dataPersistor
     * @param OrderRepository $orderRepository
     */
    public function __construct(
        DataPersistorInterface $dataPersistor,
        OrderRepository $orderRepository,
        LoggerInterface $logger,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->escaper = $escaper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            $deliveryMessage = $this->dataPersistor->get('delivery_message');

            if (!isset($deliveryMessage)) {
                //throw new StateException(__('The order message was missing.'));
                return ;
            }

            /**
             * By Abbas I am using strip_tags because I did not get any related function in Magento 2
             * In core files they are also using it. For example at vendor/magento/module-catalog/Model/Product/Option/Type/File.php Line 407
             */
            $order = $order->setData('delivery_message', $this->escaper->escapeHtml(strip_tags($deliveryMessage)));

            $this->orderRepository->save($order);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        $this->dataPersistor->clear('delivery_message');
    }
}
