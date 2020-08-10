<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 8. 10
 * Time: 오후 1:44
 */

namespace Eguana\OrderDeliveryMessage\Plugin\Sales\Api;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\OrderRepository;

/**
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
 * Class OrderManagement
 * @package Eguana\OrderDeliveryMessage\Plugin\Sales\Api
 */
class OrderManagement
{
    /**
     * Order Comment field name
     */
    const DELIVERY_MESSAGE = 'delivery_message';

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * SaveMessage constructor.
     * @param OrderRepository $orderRepository
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        OrderRepository $orderRepository,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param OrderManagementInterface $subject
     * @param OrderInterface           $order
     *
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPlace(
        OrderManagementInterface $subject,
        OrderInterface $order
    ) {
        $orderId = $order->getIncrementId();
        if ($orderId) {
            $quoteId = $order->getQuoteId();
            $quote = $this->quoteRepository->get($order->getQuoteId());
            $deliveryMessage = $quote->getData(self::DELIVERY_MESSAGE);
            $order->setData(self::DELIVERY_MESSAGE, $deliveryMessage);
            $this->orderRepository->save($order);
        }
        return $order;
    }
}