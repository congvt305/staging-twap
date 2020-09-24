<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-09-23
 * Time: 오후 6:08
 */

namespace Amore\Sap\Observer;

use Magento\Framework\Event\Observer as EventObserver;

class SourceDeductionProcessor extends \Magento\InventoryShipping\Observer\SourceDeductionProcessor
{
    public function execute(EventObserver $observer)
    {
    }
}
