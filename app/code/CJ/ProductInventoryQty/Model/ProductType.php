<?php

namespace CJ\ProductInventoryQty\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ProductType
 * @package CJ\ProductInventoryQty\Model
 */
class ProductType
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @param $productId
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getProductById($productId)
    {
        return $this->productRepository->getById($productId);
    }
}
