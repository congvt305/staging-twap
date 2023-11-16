<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Model\Category;

use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;

class PathGetter
{
    /**
     * @var CategoryInterfaceFactory
     */
    private $categoryFactory;

    /**
     * @var CategoryResource
     */
    private $categoryResource;

    /**
     * @var CategoryUrlPathGenerator
     */
    private $categoryUrlPathGenerator;

    public function __construct(
        CategoryInterfaceFactory $categoryFactory,
        CategoryResource $categoryResource,
        CategoryUrlPathGenerator $categoryUrlPathGenerator
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
    }

    /**
     * @param int $categoryId
     * @param int $storeId
     * @return string
     */
    public function execute(int $categoryId, int $storeId): string
    {
        $category = $this->categoryFactory->create()->setStoreId($storeId);
        $this->categoryResource->load($category, $categoryId);
        
        return $this->categoryUrlPathGenerator->getUrlPathWithSuffix($category);
    }
}
