<?php

declare(strict_types=1);

namespace Amasty\Meta\Model\UrlKey\Generate;

use Magento\Catalog\Api\Data\ProductInterface;

class ProductsToUpdate
{
    /**
     * @var array
     */
    private $products = [];

    /**
     * @param ProductInterface $product
     * @return void
     */
    public function addProductToUpdate(ProductInterface $product): void
    {
        $this->products[] = $product;
    }

    /**
     * @return array
     */
    public function getProductsToUpdate(): array
    {
        return $this->products;
    }

    /**
     * @return void
     */
    public function clearProductsArray(): void
    {
        $this->products = [];
    }
}
