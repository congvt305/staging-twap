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
     * @param string $source
     * @param string $mallId
     * @param mixed $orderStatusData
     * @return \Amore\Sap\Api\Data\SapOrderStatusInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function orderStatus($source, $mallId, $orderStatusData);

    /**
     * @param string $incrementId
     * @return \Amore\Sap\Api\Data\SapOrderConfirmInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function orderConfirm($incrementId);
}
