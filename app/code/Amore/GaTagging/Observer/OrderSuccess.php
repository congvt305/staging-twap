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
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(\Magento\Framework\Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     *  fired from \Magento\Checkout\Controller\Onepage\Success::execute
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->registry->register(
            \Amore\GaTagging\Block\GaTagging::PURCHASE_DATA_REGISTRY_NAME,
            $order
        );
        return $this;
    }
}
