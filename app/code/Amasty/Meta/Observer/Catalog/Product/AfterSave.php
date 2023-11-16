<?php

declare(strict_types=1);

namespace Amasty\Meta\Observer\Catalog\Product;

use Amasty\Meta\Model\UrlKey\Generate\ProcessProductsAfterCreation;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AfterSave implements ObserverInterface
{
    /**
     * @var ProcessProductsAfterCreation
     */
    private $processProductsAfterCreation;

    public function __construct(
        ProcessProductsAfterCreation $processProductsAfterCreation
    ) {
        $this->processProductsAfterCreation = $processProductsAfterCreation;
    }

    /**
     * event name: catalog_product_save_after
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $this->processProductsAfterCreation->execute();
    }
}
