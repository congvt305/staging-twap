<?php
declare(strict_types=1);

namespace Amore\Persistent\Plugin;

use Magento\Persistent\Helper\Data;

class SetQuotePersistentDataObserver
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * SetQuotePersistentDataObserver constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\PersistentHistory\Observer\SetQuotePersistentDataObserver $subject
     * @param $proceed
     * @param $observer
     * @return void
     */
    public function aroundExecute(
        \Magento\PersistentHistory\Observer\SetQuotePersistentDataObserver $subject,
        $proceed,
        $observer
    ) {
        if ($this->helper->isShoppingCartPersist()) {
            $proceed($observer);
        }
    }
}
