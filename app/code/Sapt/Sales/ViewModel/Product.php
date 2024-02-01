<?php
declare(strict_types=1);

namespace Sapt\Sales\ViewModel;

use Amasty\AdvancedReview\Block\Widget\ProductReviews\Form;
use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class Product extends \Magento\Catalog\ViewModel\Product\OptionsData
{

    /**
     * @var ImageBuilder
     */
    protected $imageBuilder;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItem;

    /**
     * @var Cart
     */
    protected $_cartHelper;
    /**
     * @var PostHelper
     */
    protected $postHelper;

    /**
     * @var \Amasty\Promo\Model\Order\Item\PromoChecker
     */
    private $amastyPromoChecker;

    /**
     * @param ImageBuilder $imageBuilder
     * @param Cart $_cartHelper
     * @param OrderItemRepositoryInterface $orderItem
     * @param PostHelper $postHelper
     * @param \Amasty\Promo\Model\Order\Item\PromoChecker $amastyPromoChecker
     */
    public function __construct(
        ImageBuilder $imageBuilder,
        Cart $_cartHelper,
        OrderItemRepositoryInterface $orderItem,
        PostHelper $postHelper,
        \Amasty\Promo\Model\Order\Item\PromoChecker $amastyPromoChecker
    ) {
        $this->imageBuilder = $imageBuilder;
        $this->_cartHelper = $_cartHelper;
        $this->orderItem = $orderItem;
        $this->postHelper = $postHelper;
        $this->amastyPromoChecker = $amastyPromoChecker;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param AbstractBlock $block
     * @return string
     */
    public function getPostData($product, $block)
    {
        return json_decode($this->postHelper->getPostData(
            $block->escapeUrl($this->getAddToCartUrl($product)), ['product' => $product->getEntityId()]
        ), true);
    }

    /**
     * @param AbstractBlock $block
     * @return bool
     */
    public function getPrintStatus($block)
    {
        return $block->getRequest()->getActionName() == 'print';
    }

    /**
     * Retrieve url for add product to cart
     *
     * Will return product view page URL if product has required options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        if (!$product->getTypeInstance()->isPossibleBuyFromList($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = [];
            }
            $additional['_query']['options'] = 'cart';

            return $this->getProductUrl($product, $additional);
        }
        return $this->_cartHelper->getAddUrl($product, $additional);
    }

    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional the route params
     * @return string
     */
    public function getProductUrl($product, $additional = [])
    {
        if ($this->hasProductUrl($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            return $product->getUrlModel()->getUrl($product, $additional);
        }

        return '#';
    }

    /**
     * Check Product has URL
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasProductUrl($product)
    {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get OrderItem
     *
     * @param int $orderItemId
     * @return Item
     */
    public function getOrderItem($orderItemId)
    {
        return $this->orderItem->get($orderItemId);
    }

    /**
     * Get Add Review Form
     *
     * @param Item $orderItem
     * @param AbstractBlock $parentBlock
     * @return string
     */
    public function getAddReviewForm($orderItem, $parentBlock)
    {
        $component = ['components' => ['review-form' => ['component' => 'Magento_Review/js/view/review']]];
        $product = $orderItem->getProduct();
        return $parentBlock->getLayout()->createBlock(Form::class)
            ->setTemplate('Magento_Review::form_mypage.phtml')
            ->setData('jsLayout', $component)
            ->setProduct($product)
            ->setData('order_item_id',$orderItem->getId())
            ->toHtml();
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->create($product, $imageId, $attributes);
    }

    /**
     * Check is promo order item
     *
     * @param $item
     * @return bool
     */
    public function isPromoItem($item)
    {
        return $this->amastyPromoChecker->isPromoItem($item);
    }

    /**
     * Check if product is active and visible on storefront
     *
     * @param $product
     * @return bool
     */
    public function isActiveProduct($product)
    {
        return $product->isInStock() && $product->isVisibleInSiteVisibility();
    }
}
