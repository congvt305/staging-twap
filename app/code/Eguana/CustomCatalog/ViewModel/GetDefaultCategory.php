<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-08-19
 * Time: 오후 9:01
 */

namespace Eguana\CustomCatalog\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class GetDefaultCategory implements ArgumentInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * ListProduct constructor.
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function getProductByDefault($sku)
    {
        return $this->productRepository->get($sku, false, 0);
    }

    public function getDefaultCategory($categoryId)
    {
        return $this->categoryRepository->get($categoryId, 0);
    }

}
