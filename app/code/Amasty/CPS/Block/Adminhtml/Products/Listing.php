<?php

namespace Amasty\CPS\Block\Adminhtml\Products;

use Amasty\CPS\Model\Product\AdminhtmlDataProvider;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Listing extends \Magento\Backend\Block\Widget\Grid
{
    public const IMAGE_WIDTH = 130;
    public const IMAGE_HEIGHT = 130;
    public const DEFAULT_SEARCH_POSITION = 1;
    public const DEFAULT_PER_PAGE_VALUES = [20, 30, 50, 100, 200];

    /**
     * @var \Magento\Framework\Data\Collection
     */
    private $collection;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $catalogImage = null;

    /**
     * @var AdminhtmlDataProvider
     */
    private $dataProvider;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var array
     */
    private $usableAttributes = [
        'name',
        'sku',
        'price'
    ];

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    private $defaultStore;

    /**
     * @var string
     */
    private $searchQuery;

    /**
     * @var array
     */
    private $resultIds = [];

    /**
     * @var array
     */
    private $invisibleItems = null;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $emulation;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Helper\Image $catalogImage,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\App\Emulation $emulation,
        AdminhtmlDataProvider $dataProvider,
        ObjectFactory $objectFactory,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->setTemplate('Amasty_VisualMerchUi::product/listing.phtml');
        $this->catalogImage = $catalogImage;
        $this->dataProvider = $dataProvider;
        $this->scopeConfig = $context->getScopeConfig();
        $this->registry = $registry;
        $this->defaultStore = current($context->getStoreManager()->getStores());
        $this->_defaultLimit = $this->scopeConfig->getValue('catalog/frontend/grid_per_page');
        $this->emulation = $emulation;
        $this->objectFactory = $objectFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('position');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
    }

    /**
     * @return \Magento\Catalog\Helper\Image
     */
    public function getImageHelper()
    {
        return $this->catalogImage;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getImageUrl($product)
    {
        $image = $this->getImageHelper()
            ->init($product, 'small_image', ['type' => 'small_image'])
            ->resize(self::IMAGE_WIDTH, self::IMAGE_HEIGHT);
        return $image->getUrl();
    }

    /**
     * Initialize grid
     *
     * @return void
     */
    protected function _prepareGrid()
    {
        $this->_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->dataProvider->getProductCollection();
        $ids = array_flip($this->dataProvider->getProductIds());
        if ($ids) {
            $collection->getSelect()->order(
                new \Zend_Db_Expr('FIELD(e.entity_id, ' . implode(',', $ids).')')
            );
        }

        $this->setCollection($collection);
        $this->_preparePage();

        foreach ($collection as $item) {
            if (!empty($this->resultIds)) {
                $item->setIsSearchResult(in_array($item->getId(), $this->resultIds));
            }
            if (array_key_exists($item->getId(), $this->dataProvider->getProductPositionData())) {
                $item->setIsManual(true);
            } else {
                $item->setIsManual(false);
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->getCollection()->getCurPage();
    }

    /**
     * @return int
     */
    public function getLastPageNumber()
    {
        if ($this->getCollection()->count()) {
            return $this->getCollection()->getLastPageNumber();
        }
        return (int)$this->_defaultPage;
    }

    /**
     * @return bool
     */
    public function isFirstPage()
    {
        return $this->getParam($this->getVarNamePage(), $this->_defaultPage) == $this->_defaultPage;
    }

    /**
     * @return int
     */
    public function getPageSize()
    {
        return $this->getCollection()->getPageSize();
    }

    /**
     * Set collection object
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return void
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    /**
     * get collection object
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Retrieve column by id
     *
     * @param string $columnId
     * @return \Magento\Framework\View\Element\AbstractBlock|bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getColumn($columnId)
    {
        return false;
    }

    /**
     * Retrieve list of grid columns
     *
     * @return array
     */
    public function getColumns()
    {
        return [];
    }

    /**
     * Process column filtration values
     *
     * @param mixed $data
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _setFilterValues($data)
    {
        return $this;
    }

    /**
     * @return array
     */
    private function getUsableAttributes()
    {
        return $this->usableAttributes;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAttributesToDisplay($product)
    {
        $attributeCodes = $this->getUsableAttributes();
        $filteredAttributes = [];

        foreach ($attributeCodes as $code) {
            $filteredAttributes[] = $this->createAttribute($product, $code);
        }

        return $filteredAttributes;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param string $code
     * @return \Magento\Framework\DataObject
     */
    private function createAttribute($product, $code = '')
    {
        $renderer = $this->objectFactory->create();
        if ($code == 'price') {
            $price = (float) $product->getData('price') ?: $product->getData('min_price');
            $attribute = $renderer->setData([
                'code' => $code,
                'value' => $this->priceCurrency->format($price)
            ]);
        } else {
            $attribute = $renderer->setData([
                'code' => $code,
                'value' => $product->getData($code)
            ]);
        }

        return $attribute;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function renderStock(\Magento\Catalog\Model\Product $product)
    {
        return $product->getData('is_salable') ? __('In Stock') : __('Out of Stock');
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        $storeId = (int)$this->_request->getParam('store');
        $params = [
            'store' => $storeId,
            'option_id' => $this->getRequest()->getParam('option_id')
        ];

        return $this->getUrl('amasty_cps/product/listing', $params);
    }

    /**
     * @param $searchQuery
     * @return $this
     */
    public function search($searchQuery)
    {
        $collection = $this->dataProvider->getProductCollection();
        $this->dataProvider->setCollectionOrder($collection);
        $this->searchQuery = $searchQuery;
        $pageSize = (int)$this->getParam($this->getVarNameLimit(), $this->_defaultLimit);
        $searchCollection = clone $collection;

        $searchCollection->addAttributeToFilter([
            ['attribute' => 'name', 'like' => "%$searchQuery%"],
            ['attribute' => 'sku', 'like' => "%$searchQuery%"]
        ]);

        $this->resultIds = $searchCollection->getAllIds();

        $productsPosition = $this->dataProvider->getProductIds();
        $firstSearchPosition = isset($productsPosition[current($this->resultIds)]) ?
            ++$productsPosition[current($this->resultIds)] : self::DEFAULT_SEARCH_POSITION;

        $this->_defaultPage = ceil($firstSearchPosition / $pageSize);

        return $this;
    }

    /**
     * @return array
     */
    public function getPerPageSize()
    {
        if (!$values = $this->scopeConfig->getValue('catalog/frontend/grid_per_page_values')) {
            return self::DEFAULT_PER_PAGE_VALUES;
        }

        return explode(',', $values);
    }

    /**
     * @return bool
     */
    public function isDynamicMode()
    {
        return $this->dataProvider->isDynamicMode();
    }

    /**
     * @return array|int
     */
    public function getInvisibleProductsCount()
    {
        if ($this->invisibleItems === null) {
            $this->invisibleItems = $this->dataProvider->getInvisibleProductsCount();
        }

        return $this->invisibleItems;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        $storeId = (int)$this->_request->getParam('store', $this->defaultStore->getId());
        $this->emulation->startEnvironmentEmulation($storeId);
        $html = parent::_toHtml();
        $this->emulation->stopEnvironmentEmulation();

        return $html;
    }

    /**
     * @return bool
     */
    public function isCanRemove()
    {
        return false;
    }
}
