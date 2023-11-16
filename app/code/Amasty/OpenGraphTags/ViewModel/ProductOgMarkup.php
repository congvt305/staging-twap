<?php

declare(strict_types=1);

namespace Amasty\OpenGraphTags\ViewModel;

use Amasty\OpenGraphTags\Model\Attribute\ProductProcessor;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ProductOgMarkup implements ArgumentInterface
{
    private const NOT_SELECTED_IMAGE = 'no_selection';
    
    /**
     * @var ProductProcessor
     */
    private $processor;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ImageFactory
     */
    protected $imageBuilder;

    public function __construct(
        ProductProcessor $processor,
        Registry $registry,
        ImageFactory $imageFactory
    ) {
        $this->processor = $processor;
        $this->registry = $registry;
        $this->imageFactory = $imageFactory;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->registry->registry('product');
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getCurrency(Product $product): string
    {
        return $product->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getPriceAmount(Product $product): string
    {
        return (string)$product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getAmount()->getValue();
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getOpenGraphTitle(Product $product): string
    {
        return $this->processor->getProductTitleAttributeValue($product);
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getOpenGraphDescription(Product $product): string
    {
        return $this->processor->getProductDescriptionAttributeValue($product);
    }

    /**
     * @param Product $product
     * @return string|null
     */
    public function getImageUrl(Product $product): ?string
    {
        return $product->getImage() && $product->getImage() !== self::NOT_SELECTED_IMAGE
            ? $this->imageFactory->create()->init($product, 'product_base_image')->getUrl()
            : null;
    }
}
