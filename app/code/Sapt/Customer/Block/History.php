<?php


namespace Sapt\Customer\Block;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Model\Product\ProductFrontendAction\Synchronizer;
use Magento\Catalog\Model\ProductFrontendAction;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Reports\Block\Product\Viewed;

class History extends AbstractProduct
{
    /**
     * @var ResourceConnection
     */
    protected $resource;
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var ProductVisibility
     */
    protected $_catalogProductVisibility;
    /**
     * @var Collection|AbstractDb
     */
    protected $_itemCollection;
    /**
     * @var Viewed
     */
    protected $recentlyViewed;
    /**
     * @var Synchronizer
     */
    protected $synchronizer;

    public function __construct(
        Context $context,
        Session $customerSession,
        ResourceConnection $resourceConnection,
        ProductVisibility $catalogProductVisibility,
        CollectionFactory $collectionFactory,
        Synchronizer $synchronizer,
        Viewed $recentlyViewed,
        array $data = []
    ) {
        $this->resource = $resourceConnection;
        $this->customerSession = $customerSession;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->collectionFactory = $collectionFactory;
        $this->synchronizer = $synchronizer;
        $this->recentlyViewed = $recentlyViewed;
        parent::__construct($context, $data);
    }

    /**
     * Prepare data
     *
     * @return $this
     */
    protected function _prepareData()
    {
        $productIds = $this->getProductIds();
        if ($productIds) {
            $collection = $this->collectionFactory->create();
            $this->_itemCollection = $collection
                ->addFieldToFilter('entity_id', ['in' => $productIds])
                ->addStoreFilter();

            $this->_addProductAttributesAndPrices($this->_itemCollection);
            $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

            $this->_itemCollection->load();

            foreach ($this->_itemCollection as $product) {
                $product->setDoNotUseCategoryId(true);
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    protected function getProductIds()
    {
        return $this->recentlyViewed->getItemsCollection()->getAllIds();
    }

    /**
     * Get collection items
     *
     * @return Collection
     */
    public function getItems()
    {
        if ($this->_itemCollection === null) {
            $this->_prepareData();
        }
        return $this->_itemCollection;
    }

    /**
     * Find out if some products can be easy added to cart
     *
     * @return bool
     */
    public function canItemsAddToCart()
    {
        foreach ($this->getItems() as $item) {
            if (!$item->isComposite() && $item->isSaleable() && !$item->getRequiredOptions()) {
                return true;
            }
        }
        return false;
    }
}
