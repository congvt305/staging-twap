<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 9/17/20
 * Time: 12:07 PM
 */

namespace Amore\GaTagging\Plugin;

use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

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

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @param \Amore\GaTagging\Helper\Data $data
     * @param \Magento\Catalog\Helper\Product $catalogProductHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Amore\GaTagging\Model\Ap $ap
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Amore\GaTagging\Helper\Data $data,
        \Magento\Catalog\Helper\Product $catalogProductHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Amore\GaTagging\Model\Ap $ap,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->data = $data;
        $this->catalogProductHelper = $catalogProductHelper;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->ap = $ap;
        $this->json = $json;
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
        $items = $this->getQuote()->getAllVisibleItems();

        if (is_array($result['items'])) {
            foreach ($result['items'] as $key => $itemAsArray) {
                if ($item = $this->findItemById($itemAsArray['item_id'], $items)) {
                    $result['items'][$key]['product_original_price'] = (float)$item->getRowTotal() / $item->getQty();
                    $result['items'][$key]['product_brand'] = $this->data->getSiteName();
                    $result['items'][$key]['product_category'] = $this->data->getProductCategory($item->getProduct());
                    $result['items'][$key]['image_url'] = $this->getProductImage($item->getProduct()->getId());
                    $result['items'][$key]['apg_brand_code'] = $this->data->getApgBrandCode($item->getSku());
                    $result['items'][$key]['discount_price'] = (float)$item->getDiscountAmount() / $item->getQty();

                    if ($item->getProductType() === Type::TYPE_BUNDLE) {
                        $itemOptions = $item->getOptionsByCode();
                        if (!empty($itemOptions['bundle_selection_ids']->getValue())) {
                            $result['items'][$key]['bundle_options'] = $this->json->unserialize(
                                $itemOptions['bundle_selection_ids']->getValue()
                            );
                        }

                        $childSkus = $childPrices = $childDiscountPrices = $childQtys = $gifts = [];
                        foreach ($item->getChildren() as $bundleChild) {
                            if ($item->getProduct()->getPriceType() == Price::PRICE_TYPE_DYNAMIC) {
                                // no need to reset because parent discount always 0
                                $result['items'][$key]['discount_price'] += $bundleChild->getDiscountAmount() / $item->getQty();
                            }

                            $childSkus[] = $bundleChild->getProduct()->getSku();
                            $childPrices[] = (float) $bundleChild->getPrice();
                            $childDiscountPrices[] = (float) $bundleChild->getDiscountAmount() / $bundleChild->getQty();
                            $childQtys[] = $bundleChild->getQty();

                            if ($bundleChild->getIsFreeGift()) {
                                $gifts[] = $bundleChild->getProduct()->getSku();
                            }
                        }

                        $result['items'][$key]['parent_sku'] = $item->getProduct()->getData('sku');
                        $result['items'][$key]['child_skus'] = implode(' / ', $childSkus);
                        $result['items'][$key]['child_prices'] = implode(' / ', $childPrices);
                        $result['items'][$key]['child_discount_prices'] = implode(' / ', $childDiscountPrices);
                        $result['items'][$key]['child_qtys'] = implode(' / ', $childQtys);
                        $result['items'][$key]['gifts'] = implode(' / ', $gifts);
                    } elseif ($item->getProductType() === Configurable::TYPE_CODE) {
                        $result['items'][$key]['parent_sku'] = $item->getProduct()->getData('sku');
                        $result['items'][$key]['child_skus'] = $item->getSku();
                        $result['items'][$key]['child_prices'] = (float)$item->getPrice();
                        $result['items'][$key]['child_discount_prices'] = (float)$item->getDiscountAmount() / $item->getQty();
                        $result['items'][$key]['child_qtys'] = $item->getQty();
                        $result['items'][$key]['gifts'] = '';
                        $result['items'][$key]['variant'] = $this->data->getSelectedOption($item);
                    }

                    $result['items'][$key]['price'] = $result['items'][$key]['product_original_price'] - $result['items'][$key]['discount_price'];
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
