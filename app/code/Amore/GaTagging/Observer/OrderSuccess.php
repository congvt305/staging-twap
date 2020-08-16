<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 8/15/20
 * Time: 8:39 PM
 */

namespace Amore\GaTagging\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class OrderSuccess implements ObserverInterface
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    public function __construct(\Magento\Framework\View\LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $block = $this->layout->getBlock('amore_gatagging');
        if ($block) {
            $block->setOrderIds($orderIds);
        }
    }
}
