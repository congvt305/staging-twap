<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/2/20
 * Time: 7:06 AM
 */

namespace Amore\GaTagging\Helper;

use Amore\GaTagging\Model\CommonVariable;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Store\Model\Store;

class Data extends AbstractHelper
{
    const XML_PATH_SITE_NAME = 'amore_gatagging/tagmanager/site_name';
    const XML_PATH_IS_ENABLED = 'amore_gatagging/tagmanager/active';
    const XML_PATH_CONTAINER_ID = 'amore_gatagging/tagmanager/container_id';
    const XML_PATH_ADDITIONAL_CONTAINER_ID = 'amore_gatagging/tagmanager/additional_container_id';
    const XML_PATH_ADDITIONAL_CONTAINER_ENABLED = 'amore_gatagging/tagmanager/additional_container_enabled';
    const XML_PATH_DATA_ENV = 'amore_gatagging/tagmanager/data_env';

    private $productCategoryPaths = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Tree
     */
    protected $categoryTree;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @param Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
    ) {
        $this->storeManager = $storeManager;
        $this->categoryTree = $categoryTree;
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
     * @return string
     */
    public function getDataEnvironment()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DATA_ENV,
            ScopeInterface::SCOPE_WEBSITE
        ) ?? CommonVariable::ENV_STG;
    }

    /**
     * @return string
     */
    public function getDataCountry()
    {
        return $this->scopeConfig->getValue(
            Custom::XML_PATH_GENERAL_COUNTRY_DEFAULT,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return string
     */
    public function getDataLanguage()
    {
        return $this->scopeConfig->getValue(
            Custom::XML_PATH_GENERAL_LOCALE_CODE,
            ScopeInterface::SCOPE_WEBSITE
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
        $store = $this->storeManager->getStore();
        if (!empty($this->getCategoryPath($product->getId(), $store->getId()))) {
            return $this->getCategoryPath($product->getId(), $store->getId());
        }

        $categoriesCollection = $product->getCategoryCollection()
            ->addIsActiveFilter()
            ->addNameToResult()
            ->setStoreId($store->getId());
        $categoriesCollection
            ->addPathsFilter(Category::TREE_ROOT_ID . '/' . $store->getRootCategoryId() . '/')
            ->getSelect()
            ->order('LENGTH(path) DESC');
        $category = $categoriesCollection->getFirstItem();

        if ($category->getId()) {
            $defaultStoreId = Store::DEFAULT_STORE_ID;

            $category = $this->categoryRepository->get($category->getId(), $defaultStoreId);
            $categoryTree = $this->categoryTree->setStoreId($defaultStoreId)
                ->loadBreadcrumbsArray($category->getPath());

            $categoryTreeNames = [];
            foreach ($categoryTree as $categoryItem) {
                if (empty(($categoryItem['name']))) {
                    continue;
                }
                $categoryTreeNames[] = $categoryItem['name'];
            }
            $categoryName = implode('/', $categoryTreeNames);

            $this->setCategoryPath($product->getId(), $store->getId(), $categoryName);
        }
        return $categoryName;
    }

    /**
     * Get category path from cache
     *
     * @param $productId
     * @param $storeId
     * @return mixed|null
     */
    protected function getCategoryPath($productId, $storeId)
    {
        if (isset($this->productCategoryPaths[$storeId][$productId])) {
            return $this->productCategoryPaths[$storeId][$productId];
        }

        return null;
    }

    /**
     * Set category path to cache
     *
     * @param $productId
     * @param $storeId
     * @param $value
     * @return void
     */
    protected function setCategoryPath($productId, $storeId, $value)
    {
        $this->productCategoryPaths[$storeId][$productId] = $value;
    }

    /**
     * @param $storeId
     * @return string
     */
    public function getSapSkuPrefix($storeId = null)
    {
        return $this->scopeConfig->getValue(
            \Amore\Sap\Model\Source\Config::SAP_SKU_PREFIX_XML_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?? '';
    }

    /**
     * @param $productSku
     * @return string
     */
    public function getSapSku($productSku)
    {
        $skuPrefix = $this->getSapSkuPrefix();
        return str_replace($skuPrefix, '', $productSku);
    }

    /**
     * @param $productSku
     * @return string
     */
    public function getApgBrandCode($productSku)
    {
        $productSku = $this->getSapSku($productSku);
        return !empty($productSku) ? substr($productSku, 0, 5) : '';
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

    /**
     * @param $item
     * @return string
     */
    public function getSelectedOption($item)
    {
        $selectedOptions = [];
        try {
            $selectedAttributes = $item->getProduct()->getTypeInstance(true)->getSelectedAttributesInfo($item->getProduct());
        } catch (\Exception $e) {
            $selectedAttributes = [];
        }

        if (empty($selectedAttributes) && isset($item->getProductOptions()['attributes_info'])) {
            $selectedAttributes = $item->getProductOptions()['attributes_info'];
        }

        if (!empty($selectedAttributes)) {
            foreach ($selectedAttributes as $attribute) {
                if (empty($attribute['value'])) {
                    continue;
                }
                $selectedOptions[] = $attribute['value'];
            }
        }

        return implode('|', $selectedOptions);
    }
}
