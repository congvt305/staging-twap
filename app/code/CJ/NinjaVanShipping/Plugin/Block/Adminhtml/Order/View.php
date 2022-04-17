<?php

namespace CJ\NinjaVanShipping\Plugin\Block\Adminhtml\Order;

class View
{
    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $subject)
    {
        $order = $subject->getOrder();
        $shippingMethod = $order->getShippingMethod();
        if ($shippingMethod === 'ninjavan_tablerate' && $order->getStatus() === 'processing_with_shipment' && !$order->getData('ninjavan_shipment_cancel')){
            $message = __('Do you want to cancel the Ninja Van shipment?');
            $url = $subject->getUrl('ninjavan/index/cancelshipment', ['order_id' => $order->getId()]);


            $subject->addButton(
                'cancel_shipment',
                [
                    'label' => __('Cancel Shipment'),
                    'class' => 'cancel_shipment',
                    'onclick' => "confirmSetLocation('{$message}', '{$url}')"
                ]
            );
        }
    }
}
