<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 20/4/21
 * Time: 10:10 PM
 */
namespace Eguana\ChangeStatus\Cron;

use Eguana\ChangeStatus\Model\GetCompletedOrders;

/**
 * Class ChangeStatusToDeliveryCompleteForTWBlackCat
 *
 * Cron class to change orders status from Shipment Processing to Delivery Complete for TW black cat
 */
class ChangeStatusToDeliveryCompleteForTWBlackCat
{
    /**
     * @var GetCompletedOrders
     */
    private $completedOrders;

    /**
     * @param GetCompletedOrders $completedOrders
     */
    public function __construct(
        GetCompletedOrders $completedOrders
    ) {
        $this->completedOrders = $completedOrders;
    }

    /**
     * To change orders status from Shipment Processing to Delivery Complete for TW black cat
     *
     * @return void
     */
    public function execute()
    {
        $this->completedOrders->changeStatusToDeliveryCompleteForBlackCat();
    }
}
