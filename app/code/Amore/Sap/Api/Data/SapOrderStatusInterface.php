<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-01
 * Time: 오후 12:12
 */

namespace Amore\Sap\Api\Data;

interface SapOrderStatusInterface
{
    const ORDER_ID = 'order_id';

    const INCREMENT_ID = 'increment_id';

    const STORE_ID = 'store_id';

    const STATUS = 'status';

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $orderId
     */
    public function setOrderId($orderId);

    /**
     * @return string
     */
    public function getIncrementId();

    /**
     * @param string $incrementId
     */
    public function setIncrementId($incrementId);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     */
    public function setStoreId($storeId);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     */
    public function setStatus($status);
}
