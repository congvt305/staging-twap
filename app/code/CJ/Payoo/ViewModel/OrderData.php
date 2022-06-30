<?php

namespace CJ\Payoo\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;

class OrderData implements ArgumentInterface
{
    /**
     * @var Registry
     */
    private Registry $registry;

    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * @return mixed|null
     */
    public function getOrder()
    {
        return $this->registry->registry('current_order');
    }

    /**
     * @param $order
     * @return mixed|null
     */
    public function getLinkPayoo($order)
    {
        $payment = $order->getPayment();
        $response = $payment->getAdditionalInformation('RESULT');
        return $response['order']['payment_url'] ?? null;
    }

    /**
     * @param $order
     * @return bool
     */
    public function isPay($order)
    {
        return !($order->getState() == Order::STATE_PAYMENT_REVIEW);
    }
}
