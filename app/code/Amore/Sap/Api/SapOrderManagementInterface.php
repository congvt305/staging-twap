<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-02
 * Time: 오후 5:56
 */

namespace Amore\Sap\Api;

/**
 * Sap Order management interface.
 * @api
 */
interface SapOrderManagementInterface
{
    const SAP_ORDER_STATUS_CREATION = 1;
    const SAP_ORDER_STATUS_CREATION_ERROR = 2;
    const SAP_ORDER_STATUS_DELIVERY_CREATION = 3;
    const SAP_ORDER_STATUS_DELIVERY_START_OR_PRODUCT_RETURNED = 4;
    const SAP_ORDER_STATUS_ORDER_CANCEL = 9;
    /**
     * @param \Amore\Sap\Api\Data\SapOrderStatusInterface $orderStatusData
     * @return \Amore\Sap\Api\Data\SapOrderStatusInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function orderStatus($orderStatusData);
}
