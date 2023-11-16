<?php

declare(strict_types=1);

namespace Amasty\Meta\Observer\Catalog\Product;

use Amasty\Meta\Model\UrlKey\Generate\ProductsToUpdate;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class BeforeSave implements ObserverInterface
{
    /**
     * @var ProductsToUpdate
     */
    private $productsToUpdate;

    public function __construct(
        ProductsToUpdate $productsToUpdate
    ) {
        $this->productsToUpdate = $productsToUpdate;
    }

    /**
     * event name: catalog_product_save_before
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $product = $observer->getProduct();
        
        if (!$product->getId()) {
            $this->productsToUpdate->addProductToUpdate($product);
        }
    }
}
