<?php

namespace CJ\OrderCancel\Plugin;

use Magento\Sales\Model\Order;

class CanCancel
{
    /**
     * Plugin for \Magento\Sales\Model\Order::canCancel
     *
     * @param Order $subject
     * @param bool $result
     * @return bool|mixed
     */
    public function afterCanCancel(Order $subject, bool $result)
    {
        if ($result === false &&
            $subject->getState() == Order::STATE_PROCESSING &&
            $subject->getStatus() == 'processing'
        ) {
            $result = true;
        }

        return $result;
    }
}
