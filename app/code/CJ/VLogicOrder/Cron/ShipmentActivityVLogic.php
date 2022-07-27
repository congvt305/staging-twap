<?php

namespace CJ\VLogicOrder\Cron;

use CJ\VLogicOrder\Model\ShipmentActivity;

class ShipmentActivityVLogic
{

    /**
     * @var ShipmentActivity
     */
    private $shipmentActivity;

    /**
     * @param ShipmentActivity $shipmentActivity
     */
    public function __construct(
        ShipmentActivity $shipmentActivity
    ){
        $this->shipmentActivity = $shipmentActivity;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->shipmentActivity->execute();
    }

}
