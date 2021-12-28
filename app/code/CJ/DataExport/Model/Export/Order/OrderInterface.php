<?php

namespace CJ\DataExport\Model\Export\Order;

/**
 * Interface OrderInterface
 */
interface OrderInterface
{
    const ORDER_ID = 'increment_id';
    const PURCHASE_DATE = 'purchase_date';
    const GRAND_TOTAL = 'grand_total';
    const STATUS = 'status';
    const SHIPPING_INFORMATION = 'shipping_information';
    const PAYMENT_METHOD = 'payment_method';
}
