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
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     * @param \Amore\GaTagging\Helper\Data $helper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Helper\Product $catalogProductHelper
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Amore\GaTagging\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Product $catalogProductHelper,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->catalogProductHelper = $catalogProductHelper;
        $this->categoryRepository = $categoryRepository;
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
        /** @var \Magento\Bundle\Model\Product\Type $bundleType */
        $bundleType = $product->getTypeInstance();
        $optionIds = $bundleType->getOptionsIds($product);
        $selections = $bundleType->getSelectionsCollection($optionIds, $product);
        $selectionProducts = [];
        $selectionsTotal = 0;
        foreach ($selections as $selection) {
            $selectionProduct = $this->productRepository->getById($selection->getProductId());
            $selectionProducts[$selection->getProductId()]['product'] = $selectionProduct;
            $selectionProducts[$selection->getProductId()]['qty'] = $selection->getSelectionQty();
            $selectionsTotal += $selectionProduct->getPrice() * $selection->getSelectionQty();
        }
        foreach ($selectionProducts as $productId => $productInfo) {
            $product = $productInfo['product'];
            if ($selectionsTotal != 0) {
                $productInfos[] = $this->getSimpleProductInfo($product, $productInfo['qty'], $product->getPrice() / $selectionsTotal * $productInfo['qty']);
            }
        }
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
        $childrenIds = $product->getTypeInstance()->getChildrenIds($product->getId());
        $childrenIds = reset($childrenIds);
        foreach ($childrenIds as $key => $childProductId) {
            $childProduct = $this->productRepository->getById($childProductId);
            $productInfos[] = $this->getSimpleProductInfo($childProduct);
        }
        return $productInfos;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return string|null
     */
    public function getProductCategory(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $categoryIds = $product->getCategoryIds();
        if ($categoryIds) {
            $categoryId = reset($categoryIds);
            return $this->_getRootCategoryName($categoryId, $product->getStoreId());
        }

        return '스킨케어';
    }

    /**
     * @param $id
     * @param $storeId
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getRootCategoryName($id, $storeId)
    {
        $categoryInstance = $this->categoryRepository->get($id, $storeId);
        $level = $categoryInstance->getLevel();
        if ($level > 2) {
            $parentId = $categoryInstance->getParentId();
            return $this->_getRootCategoryName($parentId, $storeId);
        } elseif ($level == 2) {
            return $categoryInstance->getName();
        } else {
            return '스킨케어';
        }
    }

}
