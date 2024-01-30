<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/2/20
 * Time: 7:06 AM
 */

namespace Amore\GaTagging\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_SITE_NAME = 'amore_gatagging/tagmanager/site_name';
    const XML_PATH_IS_ENABLED = 'amore_gatagging/tagmanager/active';
    const XML_PATH_CONTAINER_ID = 'amore_gatagging/tagmanager/container_id';
    const XML_PATH_ADDITIONAL_CONTAINER_ID = 'amore_gatagging/tagmanager/additional_container_id';
    const XML_PATH_ADDITIONAL_CONTAINER_ENABLED = 'amore_gatagging/tagmanager/additional_container_enabled';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @param Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
    ) {
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context);
    }

    public function getSiteName()
    {
            return $this->scopeConfig->getValue(
                self::XML_PATH_SITE_NAME,
                ScopeInterface::SCOPE_STORE
            );
    }
    public function isActive()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_IS_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getContainerId()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONTAINER_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isAdditionalContainerEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADDITIONAL_CONTAINER_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getAdditionalContainerId()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADDITIONAL_CONTAINER_ID,
            ScopeInterface::SCOPE_STORE
        );
    }


    /**
     * @param $product
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductCategory($product)
    {
        $categoryName = "";
        $categoryIds = $product->getCategoryIds();
        foreach ($categoryIds as $categoryId) {
            $nearRootCategoryId = $this->nearRootCategoryId($categoryId);
            if ($nearRootCategoryId) {
                $category = $this->categoryRepository->get($nearRootCategoryId);
                $categoryName = $category->getName();
                break;
            }
        }
        return $categoryName;
    }

    /**
     * @param $categoryId
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    function nearRootCategoryId($categoryId)
    {
        $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();
        $category = $this->categoryFactory->create()->load($categoryId);
        $parentCategories = $category->getParentCategories();
        foreach ($parentCategories as $parentCategory) {
            if ($parentCategory->getParentId() == $rootCategoryId) {
                return $parentCategory->getId();
            }
        }
        return 0;
    }


    /**
     * @param $currentProduct
     * @return mixed
     */
    public function getProductOriginalPrice($currentProduct)
    {
        $productType = $currentProduct->getTypeId();
        if ($productType == 'bundle') {
            $originalPrice = intval($currentProduct->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue());
        } else if ($productType == 'configurable') {
            $originalPrice = intval($currentProduct->getPriceInfo()->getPrice('regular_price')->getMinRegularAmount()->getValue());
        } else {
            $originalPrice = intval($currentProduct->getPrice());
        }

        return $originalPrice;
    }

    /**
     * @param $currentProduct
     * @return float|mixed
     */
    public function getProductDiscountedPrice($currentProduct)
    {
        $productType = $currentProduct->getTypeId();
        if ($productType == 'bundle') {
            $price = intval($currentProduct->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue());
        } elseif ($productType == 'configurable'){
            $price = intval($currentProduct->getPriceInfo()->getPrice('final_price')->getValue());
        } else {
            $price = intval($currentProduct->getFinalPrice());
        }

        return $price;
    }
}
