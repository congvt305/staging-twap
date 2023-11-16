<?php

namespace Amasty\CPS\Observer\Admin;

use Amasty\CPS\Model\Indexer\DataHandler;
use Magento\Framework\Event\ObserverInterface;

class SectionChanged implements ObserverInterface
{
    public const BRAND_PATH = 'amshopby_brand/general/attribute_code';

    /**
     * @var DataHandler
     */
    private $dataHandler;

    public function __construct(DataHandler $dataHandler)
    {
        $this->dataHandler = $dataHandler;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $changed = $observer->getData('changed_paths');
        if (is_array($changed) && in_array(self::BRAND_PATH, $changed)) {
            $this->dataHandler->reindexAll();
        }
    }
}
