<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 8/26/20
 * Time: 5:57 PM
 */

namespace Eguana\GWLogistics\Controller\Test;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;

class OrderItemTest extends Action
{
    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        ShipmentRepositoryInterface $shipmentRepository,
        OrderRepositoryInterface $orderRepository,
        Context $context
    )
    {
        parent::__construct($context);
        $this->shipmentRepository = $shipmentRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $this->shipmentId = intval($shipmentId);
            $shipment = $this->shipmentRepository->get($this->shipmentId);
            $orderId = $shipment->getOrderId();
            /** @var \Magento\Sales\Api\Data\OrderInterface $order */
            $order = $this->orderRepository->get(intval($orderId));
            $items = $this->getItemData($order);
//            $firstItem = $this->getItemData($order);
            var_dump($items);

    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    private function getItemData($order)
    {
        /** @var OrderInterface $order */
        $orderItems = $order->getItems();
        $firstItem = reset($orderItems);
        $count = $order->getTotalItemCount();
        //'/[\^\'`\!@#%&\*\+\\\"<>\|_\[\]]+/'
        $goodsName = str_replace(['^', '`', '\'', '!', '@','#','%', '&', '\\', '"', '<', '>', '|', '_', '[', ']',   '+', '*'], '', $firstItem->getName());
        $goodsName = (strlen($goodsName) > 30) ? substr($goodsName,0,30).'...': $goodsName;
        $goodsName = $count > 1 ? $goodsName . __(' and others.'): $goodsName;

        return [
            'goodsAmount' => intval($order->getSubtotal()),
            'goodsName' => $goodsName,
            'quantity' => $order->getTotalItemCount(),
            'cost' => intval($order->getGrandTotal()),
        ];
    }
}