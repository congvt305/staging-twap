<?php

declare(strict_types=1);

namespace Amasty\Meta\Observer\Catalog\Product;

use Amasty\Meta\Model\UrlKey\Generate\ProcessProductsAfterCreation;
use Amasty\Meta\Model\UrlKey\Generate\ProductsToUpdate;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Import implements ObserverInterface
{
    /**
     * @var ProcessProductsAfterCreation
     */
    private $processProductsAfterCreation;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductsToUpdate
     */
    private $productsToUpdate;

    public function __construct(
        ProcessProductsAfterCreation $processProductsAfterCreation,
        ProductRepositoryInterface $productRepository,
        ProductsToUpdate $productsToUpdate
    ) {
        $this->processProductsAfterCreation = $processProductsAfterCreation;
        $this->productRepository = $productRepository;
        $this->productsToUpdate = $productsToUpdate;
    }

    /**
     * event name: catalog_product_save_before
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        foreach ($observer->getEvent()->getBunch() as $productData) {
            $product = $this->productRepository->get($productData[ProductInterface::SKU]);
            $this->productsToUpdate->addProductToUpdate($product);
        }

        $this->processProductsAfterCreation->execute();
    }
}
