<?php

namespace Amasty\CPS\Model\Product;

use Amasty\CPS\Model\ResourceModel\BrandProduct;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\Catalog\Model\Product\Attribute\Source\Status as SourceStock;
use Magento\Catalog\Model\Product\Visibility;

class AdminhtmlDataProvider extends \Magento\Framework\Model\AbstractModel
{
    public const DEFAULT_REQUEST_NAME = 'catalog_view_container';
    public const DEFAULT_REQUEST_LIMIT = 0;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Amasty\Xlanding\Model\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulation;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    protected $config;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $productVisibility;

    /**
     * @var \Magento\Framework\Search\Request\Config
     */
    private $searchRequestConfig;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadata;
    /**
     * @var Status
     */
    private $status;

    /**
     * @var BrandProduct
     */
    private $brandProduct;

    /**
     * @var Sorting
     */
    private $sorting;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Session $session,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Store\Model\App\Emulation $emulation,
        StoreManagerInterface $storeManager,
        Status $status,
        \Magento\Framework\EntityManager\MetadataPool $metadata,
        \Amasty\ShopbyBrand\Helper\Data $config,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\Search\Request\Config $searchRequestConfig,
        \Amasty\CPS\Model\ResourceModel\BrandProduct $brandProduct,
        \Amasty\CPS\Model\Product\Sorting $sorting,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->session = $session;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->moduleManager = $moduleManager;
        $this->emulation = $emulation;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->productVisibility = $productVisibility;
        $this->searchRequestConfig = $searchRequestConfig;
        $this->metadata = $metadata;
        $this->status = $status;
        $this->brandProduct = $brandProduct;
        $this->sorting = $sorting;
    }

    /**
     * Get the difference between all products and visible
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getInvisibleProductsCount()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId($this->getStoreId())
            ->addAttributeToFilter(
                $this->config->getBrandAttributeCode(),
                ['finset' => $this->session->getOptionId()]
            );

        return $collection->getSize() - $this->getProductCollection()->getSize();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductCollection()
    {
        if (!$this->getData('product_collection')) {
            $collection = $this->initCollection();
            $this->setData('product_collection', $collection);
        }

        return $this->getData('product_collection');
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function initCollection()
    {
        $storeId = $this->getStoreId();
        $collection = $this->productCollectionFactory->create()
            ->addStoreFilter($storeId)
            ->setStore($storeId);
        $collection = $this->addPriceToSelect($collection, $storeId);
        $collection
            ->addAttributeToFilter('visibility', ['IN' =>$this->productVisibility->getVisibleInSiteIds()])
            ->addAttributeToSelect(
                [
                    'sku',
                    'name',
                    'small_image'
                ]
            );

            $collection = $this->brandProduct->filterByBrand(
                $collection,
                $this->session->getOptionId(),
                $this->getStoreId()
            );

        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $collection = $this->status->addStockDataToCollection($collection, false);
        }
        $collection->getSelect()->limit($this->getDynamicCollectionLimit());

        return $collection;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @param int $storeId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function addPriceToSelect($productCollection, int $storeId)
    {
        if ($storeId) {
            $productCollection->addPriceData();
        } else {
            $productCollection->addAttributeToSelect('price', true);
        }

        return $productCollection;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return $this
     */
    public function setCollectionOrder($collection)
    {
        $this->sorting->applySorting($collection, $this->getSortOrder());

        return $this;
    }

    /**
     * @return array
     */
    public function getProductPositionData()
    {
        return $this->session->getPositionData() ?: [];
    }

    /**
     * @param array $productIds
     */
    public function setProductIds($productIds = [])
    {
        $this->session->setProductIds($productIds);
    }

    /**
     * @return array
     */
    public function getProductIds()
    {
        return $this->session->getProductIds() ?: [];
    }

    /**
     * @param $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->session->setStoreId((int) $storeId);

        return $this;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->session->getStoreId();
    }

    /**
     * @return bool
     */
    public function isDynamicMode()
    {
        return false;
    }

    /**
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        $this->session->setSortOrder($sortOrder);

        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return (int)$this->session->getSortOrder();
    }

    /**
     * @return int
     */
    private function getDynamicCollectionLimit()
    {
        $requestData = $this->searchRequestConfig->get(self::DEFAULT_REQUEST_NAME);

        return isset($requestData['size']) ? $requestData['size'] : self::DEFAULT_REQUEST_LIMIT;
    }

    /**
     * Clear storage data after save category
     *
     * @return $this
     */
    public function clear()
    {
        $this->session->setPositionData(null);
        $this->session->setProductIds(null);
        $this->setSortOrder(null);
        $this->setStoreId(null);

        return $this;
    }

    /**
     * @param $entity
     * @return $this
     */
    public function init($entity)
    {
        $optionId = $entity->getValue();
        $storeId = $entity->getCurrentStoreId();
        $products = $this->brandProduct->getProductPositionData($optionId, $storeId);
        $pinnedProducts = $this->brandProduct->getProductPositionData($optionId, $storeId, true);

        $this->session->setOptionId($optionId);
        $this->session->setPositionData($pinnedProducts);
        $this->session->setProductIds($products);

        $this->setSortOrder($entity->getSorting());
        $this->setStoreId($storeId);

        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function unsetProductPositionData($key)
    {
        $data = $this->getProductPositionData();

        if (isset($data[$key])) {
            unset($data[$key]);
            $this->session->setPositionData($data);
        }

        return $this;
    }

    /**
     * @param array $positionData
     * @param int $offset
     * @return $this
     */
    public function setProductPositionData($positionData = [], $offset = 1)
    {
        if (!empty($positionData)) {
            $currentPositionData = $this->session->getPositionData();

            foreach ($positionData as $productId => $position) {
                $key = array_search($position, $currentPositionData);
                if ($key && $key != $productId) {
                    $currentPositionData = $this->getUniquePosition(
                        $currentPositionData,
                        $position,
                        $offset
                    );
                }
                $currentPositionData[$productId] = $position;
            }

            $this->session->setPositionData($currentPositionData);
        }

        return $this;
    }

    /**
     * @param array $currentPositionData
     * @param int $position
     * @param int $offset
     */
    protected function getUniquePosition($currentPositionData, $position, $offset = -1)
    {
        $position = $position + $offset;

        if (in_array($position, $currentPositionData)) {
            $currentPositionData = $this->getUniquePosition($currentPositionData, $position, $offset);
        }

        $id = array_search($position - $offset, $currentPositionData);
        $currentPositionData[$id] = $position;

        return $currentPositionData;
    }

    /**
     * @return int
     */
    public function getBrandId()
    {
        return (int) $this->session->getOptionId();
    }
}
