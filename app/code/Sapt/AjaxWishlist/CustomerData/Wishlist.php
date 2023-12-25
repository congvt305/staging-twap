<?php

namespace Sapt\AjaxWishlist\CustomerData;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\CustomerData\Wishlist as WishlistData;

class Wishlist extends WishlistData
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Wishlist constructor.
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Wishlist\Block\Customer\Sidebar $block
     * @param \Magento\Catalog\Helper\ImageFactory $imageHelperFactory
     * @param \Magento\Framework\App\ViewInterface $view
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface|null $itemResolver
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Wishlist\Block\Customer\Sidebar $block,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Magento\Framework\App\ViewInterface $view,
        \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface $itemResolver = null
    )
    {
        parent::__construct($wishlistHelper, $block, $imageHelperFactory, $view, $itemResolver);
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function getSectionData()
    {
        $counter = $this->getCounter();

        $result = [
            'counter' => $counter,
            'items' => $counter ? $this->getItems() : [],
        ];
        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            $result = array_merge(
                $result,
                [
                    'all_wishlist_items' => $counter ? $this->getAllItems() : []
                ]
            );
        }

        return $result;
    }

    /**
     * Get wishlist Items
     *
     * @return array
     */
    protected function getAllItems()
    {

        $collection = $this->wishlistHelper->getWishlistItemCollection();
        $collection->clear()
            ->setInStockFilter(true)->setOrder('added_at');

        $items = [];
        foreach ($collection as $wishlistItem) {
            $product = $wishlistItem->getProduct();
            $items[$product->getId()] = $wishlistItem;
        }
        return $items;
    }
}
