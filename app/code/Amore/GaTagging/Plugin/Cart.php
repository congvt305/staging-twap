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
     * @var \Amore\GaTagging\Model\Ap
     */
    protected $ap;


    public function __construct(
        \Amore\GaTagging\Helper\Data $data,
        \Magento\Catalog\Helper\Product $catalogProductHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Amore\GaTagging\Model\Ap $ap
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->data = $data;
        $this->catalogProductHelper = $catalogProductHelper;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->ap = $ap;
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
        $items = $this->getQuote()->getAllVisibleItems();

        if (is_array($result['items'])) {
            foreach ($result['items'] as $key => $itemAsArray) {
                if ($item = $this->findItemById($itemAsArray['item_id'], $items)) {
                    $result['items'][$key]['product_original_price'] = $this->data->getProductOriginalPrice($item->getProduct());
                    $result['items'][$key]['price'] = $this->data->getProductDiscountedPrice($item->getProduct());
                    $result['items'][$key]['product_brand'] = $this->data->getSiteName();
                    $result['items'][$key]['product_category'] = $this->data->getProductCategory($item->getProduct());
                    $result['items'][$key]['image_url'] = $this->getProductImage($item->getProduct()->getId());
                    $result['items'][$key]['apg_brand_code'] = $this->data->getApgBrandCode($item->getProduct()->getSku());

                    $price = $result['items'][$key]['price'] ?? 0;
                    $originalPrice = $result['items'][$key]['product_original_price'] ?? 0;
                    $result['items'][$key]['discount_price'] = $originalPrice > $price ? ($originalPrice - $price) : 0;
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

}
