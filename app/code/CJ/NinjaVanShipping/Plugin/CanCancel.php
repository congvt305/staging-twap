<?php

namespace CJ\NinjaVanShipping\Plugin;

use Magento\Sales\Model\Order;

class CanCancel
{
    /**
     * @var \CJ\NinjaVanShipping\Helper\Data
     */
    protected $data;

    /**
     * @param \CJ\NinjaVanShipping\Helper\Data $data
     */
    public function __construct(\CJ\NinjaVanShipping\Helper\Data $data)
    {
        $this->data = $data;
    }

    /**
     * Plugin for \Magento\Sales\Model\Order::canCancel
     *
     * @param Order $subject
     * @param bool $result
     * @return bool|mixed
     */
    public function afterCanCancel(Order $subject, bool $result)
    {
        $allowOrderStatus = $this->data->getNinjaVanAllowsOrderStatusCancel();
        $allowOrderStatus = explode(",", $allowOrderStatus);

        if ($result === false
            && $subject->getState() == Order::STATE_PROCESSING
            && in_array($subject->getStatus(), $allowOrderStatus)
            && $subject->getShippingMethod() === 'ninjavan_tablerate'
        ) {
            $result = true;
        }

        return $result;
    }
}
