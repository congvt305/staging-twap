<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/2/20
 * Time: 2:44 PM
 */

namespace Amore\GaTagging\Model;


class Ap
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerializer;

    /**
     * @var \Amore\GaTagging\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalogProductHelper;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     * @param \Amore\GaTagging\Helper\Data $helper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Helper\Product $catalogProductHelper
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Amore\GaTagging\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Product $catalogProductHelper,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->catalogProductHelper = $catalogProductHelper;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * @param $product
     * @return array
     */
    public function getProductInfo($product)
    {
        $productDataArr = [];
        if ($product->getTypeId() === 'bundle') {
            $productData = $this->getBundleProductInfo($product);
            foreach ($productData as $productDatum) {
                $productDataArr[] = $this->jsonSerializer->serialize($productDatum);
            }
        } elseif ($product->getTypeId() === 'configurable') {
            $productData = $this->getConfigurableProductInfo($product);
            foreach ($productData as $productDatum) {
                $productDataArr[] = $this->jsonSerializer->serialize($productDatum);
            }
        } else {
            $productData = $this->getSimpleProductInfo($product);
            $productDataArr[] = $this->jsonSerializer->serialize($productData);
        }
        return $productDataArr;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     */
    protected function getSimpleProductInfo($product, $qty=null, $rate=null)
    {
        $productInfo = [];
        $productInfo['name'] = $product->getName();
        $productInfo['code'] = $product->getSku();
        $productInfo['v2code'] = $product->getId();
        $productInfo['sapcode'] = $product->getSku();
        $productInfo['brand'] = $this->helper->getSiteName() ?? '';
        $productInfo['prdprice'] = intval($product->getPrice());
        $productInfo['price'] = intval($product->getPriceInfo()->getPrice('final_price')->getValue());
        $productInfo['variant'] = '';
        $productInfo['promotion'] = '';
        $productInfo['cate'] = $this->getProductCategory($product);
        $productInfo['catecode'] = '';
        $productInfo['url'] = $product->getProductUrl();
        $productInfo['img_url'] = $this->catalogProductHelper->getThumbnailUrl($product);
        $productInfo['quantity'] = $qty ? intval($qty) : 0;
        if ($rate) {
            $productInfo['rate'] = $rate;
        }
        return $productInfo;
    }

    /**
     * @param $product
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getBundleProductInfo($product)
    {
        $productInfos = [];
        $productInfo = [];
        $productInfo['name'] = $product->getName();
        $productInfo['code'] = $product->getSku();
        $productInfo['v2code'] = $product->getId();
        $productInfo['sapcode'] = $product->getSku();
        $productInfo['brand'] = $this->helper->getSiteName() ?? '';
        $productInfo['prdprice'] = $this->getProductOriginalPrice($product);
        $productInfo['variant'] = '';
        $productInfo['promotion'] = '';
        $productInfo['cate'] = $this->getProductCategory($product);
        $productInfo['catecode'] = '';
        $productInfo['price'] = $this->getProductDiscountedPrice($product);
        $productInfo['url'] = $product->getProductUrl();
        $productInfo['img_url'] = $this->catalogProductHelper->getThumbnailUrl($product);
        $productInfos[] = $productInfo;
        return $productInfos;
    }

    /**
     * @param $product
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getConfigurableProductInfo($product)
    {
        $productInfos = [];
        $productInfo = [];
        $productInfo['name'] = $product->getName();
        $productInfo['code'] = $product->getSku();
        $productInfo['v2code'] = $product->getId();
        $productInfo['sapcode'] = $product->getSku();
        $productInfo['brand'] = $this->helper->getSiteName() ?? '';
        $productInfo['prdprice'] = $this->getProductOriginalPrice($product);
        $productInfo['variant'] = '';
        $productInfo['promotion'] = '';
        $productInfo['cate'] = $this->getProductCategory($product);
        $productInfo['catecode'] = '';
        $productInfo['price'] = $this->getProductDiscountedPrice($product);
        $productInfo['url'] = $product->getProductUrl();
        $productInfo['img_url'] = $this->catalogProductHelper->getThumbnailUrl($product);
        $productInfos[] = $productInfo;
        return $productInfos;
    }

    /**
     * @param $product
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductCategory($product)
    {
        $categoryName = "스킨케어";
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
