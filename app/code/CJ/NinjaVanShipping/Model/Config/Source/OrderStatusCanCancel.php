<?php

namespace CJ\NinjaVanShipping\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection as OrderStatusCollection;

class OrderStatusCanCancel implements ArrayInterface
{
    /**
     * @var OrderStatusCollection
     */
    private $orderStatusCollection;

    public function __construct(OrderStatusCollection $orderStatusCollection)
    {
        $this->orderStatusCollection=$orderStatusCollection;
    }

    public function toOptionArray()
    {
        return $this->orderStatusCollection->toOptionArray();
    }
}
