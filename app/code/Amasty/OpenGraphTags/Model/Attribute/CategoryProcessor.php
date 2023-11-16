<?php

declare(strict_types=1);

namespace Amasty\OpenGraphTags\Model\Attribute;

use Amasty\OpenGraphTags\Model\ConfigProvider;
use Amasty\OpenGraphTags\Model\Meta\GetReplacedMetaData;
use Magento\Catalog\Model\Category;

class CategoryProcessor
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetReplacedMetaData
     */
    private $getReplacedMetaData;
    
    public function __construct(
        ConfigProvider $configProvider,
        GetReplacedMetaData $getReplacedMetaData
    ) {
        $this->configProvider = $configProvider;
        $this->getReplacedMetaData = $getReplacedMetaData;
    }

    /**
     * @param Category $category
     * @return string
     */
    public function getCategoryTitleAttributeValue(Category $category): string
    {
        $attributeCode = $this->configProvider->getCategoryPageTitleAttribute();

        return $this->getAttributeValue($attributeCode, $category);
    }

    /**
     * @param Category $category
     * @return string
     */
    public function getCategoryDescriptionAttributeValue(Category $category): string
    {
        $attributeCode = $this->configProvider->getCategoryPageDescriptionAttribute();

        return $this->getAttributeValue($attributeCode, $category);
    }

    private function getAttributeValue(string $attributeCode, Category $category): string
    {
        $value = $this->getReplacedMetaData->execute($attributeCode) ?: $category->getData($attributeCode);

        return (string)$value;
    }
}
