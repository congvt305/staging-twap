<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-08-18
 * Time: 오후 5:10
 */

namespace Eguana\PendingCanceler\Cron;

use Eguana\PendingCanceler\Model\PendingOrderCanceler;

class PendingOrdersCancelCron
{
    /**
     * @var PendingOrderCanceler
     */
    private $pendingOrderCanceler;

    /**
     * PendingOrdersCancelCron constructor.
     * @param PendingOrderCanceler $pendingOrderCanceler
     */
    public function __construct(
        PendingOrderCanceler $pendingOrderCanceler
    ) {
        $this->pendingOrderCanceler = $pendingOrderCanceler;
    }

    public function execute()
    {
        $this->pendingOrderCanceler->pendingCanceler();
    }
}
