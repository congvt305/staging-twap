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
    /**
     * @param \Amore\Sap\Api\Data\SapOrderStatusInterface $orderStatusData
     * @return \Amore\Sap\Api\Data\SapOrderStatusInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function orderStatus($orderStatusData);
}
