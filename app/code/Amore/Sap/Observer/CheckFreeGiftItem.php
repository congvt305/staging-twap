<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-09-23
 * Time: 오후 6:08
 */

namespace Amore\Sap\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class CheckFreeGiftItem implements ObserverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param EventObserver $observer
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() == 'bundle') {
                continue;
            }
            $productPrice = $this->productRepository->getById($item->getProductId())->getPrice();
            if ($productPrice == 0) {
                $item->setIsFreeGift(1);
            }
        }
        $this->orderRepository->save($order);
    }
}
