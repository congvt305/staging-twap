<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 7. 13
 * Time: 오후 6:50
 */

namespace Eguana\OrderDeliveryMessage\Plugin\Sales\Model;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderExtensionInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
 * Class OrderRepository
 * @package Eguana\OrderDeliveryMessage\Plugin\Sales\Model
 */
class OrderRepositoryPlugin
{
    /**
     * Order Comment field name
     */
    const FIELD_NAME = 'delivery_message';

    /**
     * Order Extension Attributes Factory
     *
     * @var OrderExtensionFactory
     */
    protected $extensionFactory;
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * OrderRepositoryPlugin constructor
     *
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        OrderExtensionFactory $extensionFactory
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Add "order_comment" extension attribute to order data object to make it accessible in API data of order record
     *
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        /**
         * @var \Magento\Quote\Api\Data\Cart $quote
         */
        $quote = $this->quoteRepository->get($order->getQuoteId());
        $deliveryMessage = $quote->getData(self::FIELD_NAME);
        $extensionAttributes = $order->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
        $extensionAttributes->setDeliveryMessage($deliveryMessage);
        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }

    /**
     * Add "order_comment" extension attribute to order data object to make it accessible in API data of all order list
     *
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $searchResult)
    {
        $orders = $searchResult->getItems();

        foreach ($orders as &$order) {
            /**
             * @var \Magento\Quote\Api\Data\Cart $quote
             */
            $quote = $this->quoteRepository->get($order->getQuoteId());
            $deliveryMessage = $quote->getData(self::FIELD_NAME);
            $extensionAttributes = $order->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
            $extensionAttributes->setDeliveryMessage($deliveryMessage);
            $order->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }
}