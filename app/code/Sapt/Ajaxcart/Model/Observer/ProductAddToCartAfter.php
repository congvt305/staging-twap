<?php
namespace Sapt\Ajaxcart\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ProductAddToCartAfter implements ObserverInterface
{
    /**
     * Ajax cart helper.
     *
     * @var \Sapt\Ajaxcart\Helper\Data
     */
    private $helper;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Initialize dependencies.
     *
     * @param \Sapt\Ajaxcart\Helper\Data $helper
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Sapt\Ajaxcart\Helper\Data $helper,
        \Magento\Framework\Registry $registry
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
    }

    /**
     * Check is show additional data in quick view.
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if ($this->helper->isEnabled()) {
            $resultItem = $observer->getQuoteItem();
            $this->registry->register('last_added_quote_item', $resultItem);
        }
        if ($this->registry->registry('current_order')){
            $this->registry->unregister('last_added_quote_item');
        }
    }
}
