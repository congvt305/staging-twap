<?php
declare(strict_types=1);

namespace Amore\Cms\Block\Widget;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\EncoderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Block\BlockInterface;

class BlockSlider extends \Magento\Catalog\Block\Product\AbstractProduct implements BlockInterface, IdentityInterface
{

    /**
     * @var Json
     */
    private $json;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EncoderInterface
     */
    private $urlEncoder;

    /**
     * @param Context $context
     * @param Json $json
     * @param CollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param EncoderInterface $urlEncoder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Json $json,
        CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,
        EncoderInterface $urlEncoder,
        array $data = []
    ) {
        $this->json = $json;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
        $this->urlEncoder = $urlEncoder;
        parent::__construct($context, $data);
    }

    /**
     * Internal constructor, that is called from real constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addColumnCountLayoutDepend('empty', 6)
            ->addColumnCountLayoutDepend('1column', 5)
            ->addColumnCountLayoutDepend('2columns-left', 4)
            ->addColumnCountLayoutDepend('2columns-right', 4)
            ->addColumnCountLayoutDepend('3columns', 3);

        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => [
                Product::CACHE_TAG,
            ],
        ]);
    }

    /**
     * Get key pieces for caching block content
     *
     * @return array
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     * @throws NoSuchEntityException
     */
    public function getCacheKeyInfo()
    {

        return [
            'SLIDER_PRODUCTS_LIST_WIDGET',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->json->serialize($this->getRequest()->getParams()),
            $this->getTemplate()
        ];
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getProductPriceHtml(
        Product $product,
                $priceType = null,
                $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['price_id'] = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->getLayout()->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        $price = $priceRender->render(
            FinalPrice::PRICE_CODE,
            $product,
            $arguments
        );

        return $price;
    }

    /**
     * @inheritdoc
     */
    protected function _beforeToHtml()
    {
        $this->setProductCollection($this->createCollection());
        return parent::_beforeToHtml();
    }

    /**
     * Prepare and return product collection
     *
     * @return Collection
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     * @throws LocalizedException
     */
    public function createCollection()
    {
        $skus = $this->getData('skus');
        $skuArr = explode(',', $skus);
        array_walk($skuArr, function(&$sku) {
            $sku = trim($sku);
        });
        /** @var $collection Collection */
        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId($this->storeManager->getStore()->getId());

        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter()
            ->addFieldToFilter('sku', ['in' => $skuArr])
            ->addAttributeToSort('entity_id', 'desc')
            ->setPageSize(12);


        /**
         * Prevent retrieval of duplicate records. This may occur when multiselect product attribute matches
         * several allowed values from condition simultaneously
         */
        $collection->distinct(true);

        return $collection;
    }

    /**
     * Get identities of the Cms Block
     *
     * @return array
     */
    public function getIdentities()
    {
        $block = $this->getBlock();

        if ($block) {
            return $block->getIdentities();
        }

        return [];
    }

    /**
     * Get post parameters.
     *
     * @param Product $product
     * @return array
     */
    public function getAddToCartPostParams(Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlEncoder->encode($url),
            ]
        ];
    }
}
