<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 9/17/20
 * Time: 12:07 PM
 */

namespace Amore\GaTagging\Plugin;


class Cart
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    private $quote = null;
    /**
     * @var \Amore\GaTagging\Helper\Data
     */
    private $data;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalogProductHelper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Amore\GaTagging\Model\Ap
     */
    protected $ap;


    public function __construct(
        \Amore\GaTagging\Helper\Data $data,
        \Magento\Catalog\Helper\Product $catalogProductHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Amore\GaTagging\Model\Ap $ap,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->data = $data;
        $this->catalogProductHelper = $catalogProductHelper;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->ap = $ap;
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param $result
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        if (!$this->data->isActive()) {
            return $result;
        }
        $storeId =
        $items =$this->getQuote()->getAllVisibleItems();

        if (is_array($result['items'])) {
            foreach ($result['items'] as $key => $itemAsArray) {
                if ($item = $this->findItemById($itemAsArray['item_id'], $items)) {
                    $result['items'][$key]['product_original_price'] = $item->getProduct()->getPrice();
                    $result['items'][$key]['product_brand'] = $this->data->getSiteName();
                    $result['items'][$key]['product_category'] = $this->getProductCategory($item->getProduct());
                    $result['items'][$key]['image_url'] = $this->getProductImage($item->getProduct()->getId());
                }
            }
        }
        return $result;
    }

    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    private function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    private function findItemById($id, $itemsHaystack)
    {
        if (is_array($itemsHaystack)) {
            foreach ($itemsHaystack as $item) {
                /** @var $item \Magento\Quote\Model\Quote\Item */
                if ((int)$item->getItemId() == $id) {
                    return $item;
                }
            }
        }
        return false;
    }

    protected function getProductImage($productId)
    {
        $product = $this->productRepository->getById($productId);
        return $this->catalogProductHelper->getThumbnailUrl($product);
    }

    /**
     * @param $product
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductCategory($product)
    {
        $categoryNames = [];
        $categoryIds = $product->getCategoryIds();
        foreach ($categoryIds as $categoryId) {
            $nearRootCategoryId = $this->nearRootCategoryId($categoryId);
            if ($nearRootCategoryId) {
                $category = $this->categoryRepository->get($nearRootCategoryId);
                if (!in_array($category->getName(), $categoryNames)) {
                    $categoryNames[] = $category->getName();
                }
            }
        }
        return implode(",", $categoryNames);
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
}
