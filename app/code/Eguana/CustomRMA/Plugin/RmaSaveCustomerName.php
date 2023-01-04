<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 30/12/20
 * Time: 4:48 PM
 */
namespace Eguana\CustomRMA\Plugin;

use Magento\Sales\Model\Order;
use Magento\Rma\Model\Rma\RmaDataMapper;

/**
 * Class RmaSaveShippingPreference
 *
 * Save shipping preference for admin RMA
 */
class RmaSaveCustomerName
{
    /**
     * Save rma customer name for admin RMA
     * @param RmaDataMapper $subject
     * @param $result
     * @param array $saveRequest
     * @param Order $order
     * @return mixed
     */
    public function afterPrepareNewRmaInstanceData(RmaDataMapper $subject, $result, array $saveRequest, Order $order)
    {
        $result['rma_customer_name'] = $saveRequest['rma_customer_name'];
        return $result;
    }
}
