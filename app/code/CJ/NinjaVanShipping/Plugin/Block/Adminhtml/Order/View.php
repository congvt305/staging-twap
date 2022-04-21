<?php

namespace CJ\NinjaVanShipping\Plugin\Block\Adminhtml\Order;

class View
{
    protected $data;
    public function __construct(
        \CJ\NinjaVanShipping\Helper\Data $data
    ){
        $this->data = $data;
    }
    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $subject)
    {
        $order = $subject->getOrder();
        if ($this->canShowButton($order)){
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

    protected function canShowButton(\Magento\Sales\Model\Order $order)
    {
        $allowOrderStatus = $this->data->getNinjaVanAllowsOrderStatusCancel();
        $allowOrderStatus = explode(",", $allowOrderStatus);
        $shippingMethod = $order->getShippingMethod();
        if (
            $shippingMethod === 'ninjavan_tablerate' &&
            in_array($order->getStatus(), $allowOrderStatus) &&
            !$order->getData('ninjavan_shipment_cancel')
        ){
            return true;
        }
        return false;
    }
}
