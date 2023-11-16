<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Observer\GraphQl\Quote\Submit;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderCustomerDelegateInterface;

class CreateOrderCustomerDelegate implements ObserverInterface
{
    /**
     * @var OrderCustomerDelegateInterface
     */
    private $delegateService;

    public function __construct(OrderCustomerDelegateInterface $delegateService)
    {
        $this->delegateService = $delegateService;
    }

    /**
     * Event `checkout_submit_all_after`
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getDataByKey('order');
        if ($order->getCustomerIsGuest()) {
            $this->delegateService->delegateNew((int)$order->getEntityId());
        }
    }
}
