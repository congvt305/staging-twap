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
     *  fired from \Magento\Checkout\Controller\Onepage\Success::execute
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $block = $this->layout->getBlock('ap_onepage_success');
        if ($block) {
            $block->setOrder($order);
        }
    }
}
