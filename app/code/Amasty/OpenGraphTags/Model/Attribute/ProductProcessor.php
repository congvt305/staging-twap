<?php

declare(strict_types=1);

namespace Amasty\OpenGraphTags\Model\Attribute;

use Amasty\OpenGraphTags\Model\ConfigProvider;
use Amasty\OpenGraphTags\Model\Meta\GetReplacedMetaData;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class ProductProcessor
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var GetReplacedMetaData
     */
    private $getReplacedMetaData;

    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository,
        ConfigProvider $configProvider,
        LoggerInterface $logger,
        GetReplacedMetaData $getReplacedMetaData
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
        $this->configProvider = $configProvider;
        $this->logger = $logger;
        $this->getReplacedMetaData = $getReplacedMetaData;
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getProductTitleAttributeValue(Product $product): string
    {
        $attributeCode = $this->configProvider->getProductPageTitleAttribute();

        return $this->getAttributeValue($attributeCode, $product);
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getProductDescriptionAttributeValue(Product $product): string
    {
        $attributeCode = $this->configProvider->getProductPageDescriptionAttribute();

        return $this->getAttributeValue($attributeCode, $product);
    }
    
    private function getAttributeValue(string $attributeCode, Product $product): string
    {
        try {
            $attribute = $this->productAttributeRepository->get($attributeCode);
        } catch (NoSuchEntityException $e) {
            $message = __('Amasty Open Graph Tags: %1', $e->getMessage());
            $this->logger->debug($message);

            return '';
        }
        
        $metaValue = $this->getReplacedMetaData->execute($attributeCode);

        if ($metaValue) {
            $value = $metaValue;
        } elseif ($attribute->usesSource()) {
            $value = $product->getAttributeText($attributeCode);

            if (is_array($value)) {
                $value = implode(',', $value);
            }
        } else {
            $value = $product->getData($attributeCode);
        }

        return (string)$value;
    }
}
