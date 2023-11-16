<?php

declare(strict_types=1);

namespace Amasty\OpenGraphTags\ViewModel;

use Amasty\OpenGraphTags\Model\Attribute\CategoryProcessor;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Category\Image;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CategoryOgMarkup implements ArgumentInterface
{
    /**
     * @var Image
     */
    private $categoryImage;

    /**
     * @var CategoryProcessor
     */
    private $categoryProcessor;

    /**
     * @var Resolver
     */
    private $layerResolver;
    
    public function __construct(
        CategoryProcessor $categoryProcessor,
        Image $categoryImage,
        Resolver $layerResolver
    ) {
        $this->categoryProcessor = $categoryProcessor;
        $this->categoryImage = $categoryImage;
        $this->layerResolver = $layerResolver;
    }

    /**
     * @return Category
     */
    public function getCurrentCategory(): Category
    {
        return $this->layerResolver->get()->getCurrentCategory();
    }

    /**
     * @param Category $category
     * @return string
     */
    public function getCategoryImageUrl(Category $category): string
    {
        return $this->categoryImage->getUrl($category);
    }

    /**
     * @param Category $category
     * @return string
     */
    public function getOpenGraphTitle(Category $category): string
    {
        return $this->categoryProcessor->getCategoryTitleAttributeValue($category);
    }

    /**
     * @param Category $category
     * @return string
     */
    public function getOpenGraphDescription(Category $category): string
    {
        return $this->categoryProcessor->getCategoryDescriptionAttributeValue($category);
    }
}
