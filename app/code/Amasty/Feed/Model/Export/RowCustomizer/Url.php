<?php

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Amasty\Feed\Model\Config\Source\Path;
use Amasty\Feed\Model\Export\Product as ExportProduct;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Store\Model\ScopeInterface;

class Url implements RowCustomizerInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $urlRewrites;

    /**
     * @var \Magento\Framework\Url
     */
    protected $url;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var array
     */
    protected $rowCategories;

    /**
     * @var ExportProduct
     */
    protected $export;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ExportProduct $export,
        \Magento\Framework\Url $url, //always get frontend url
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Base\Model\Serializer $serializer,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->url = $url;
        $this->export = $export;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        if ($this->export->hasAttributes(ExportProduct::PREFIX_URL_ATTRIBUTE)) {
            $this->storeId = $collection->getStoreId();
            $select = $collection->getConnection()
                ->select()
                ->from(['u' => $collection->getTable('url_rewrite')], ['u.entity_id', 'u.request_path', 'u.metadata'])
                ->where('u.store_id = ?', $this->storeId)
                ->where('u.is_autogenerated = 1')
                ->where('u.entity_type = ?', ProductUrlRewriteGenerator::ENTITY_TYPE)
                ->where('u.entity_id IN(?)', $productIds);

            foreach ($collection->getConnection()->fetchAll($select) as $row) {
                $metadata = $this->serializer->unserialize($row['metadata']);
                $categoryId = is_array($metadata) && isset($metadata['category_id'])
                    ? $metadata['category_id']
                    : null;

                if (!isset($row['entity_id'])) {
                    $this->urlRewrites[$row['entity_id']] = [];
                }

                $this->urlRewrites[$row['entity_id']][(int)$categoryId] = $row['request_path'];
            }

            $multiRowData = $this->export->getMultiRowData();
            $this->rowCategories = $multiRowData['rowCategories'];
        }
    }

    /**
     * @inheritdoc
     */
    public function addHeaderColumns($columns)
    {
        return $columns;
    }

    /**
     * @inheritdoc
     */
    public function addData($dataRow, $productId)
    {
        $customData = &$dataRow['amasty_custom_data'];
        //if the production mode + config cache is enabled.
        //Store manager returns current website url, instead of one have been set.
        //taking url directly from URL model.
        $this->url->setScope($this->storeManager->getStore($this->storeId));

        if ($this->urlRewrites && isset($this->urlRewrites[$productId])) {
            $urlRewrites = $this->urlRewrites[$productId];
            $pathMode = $this->scopeConfig->getValue(
                'amasty_feed/general/category_path',
                ScopeInterface::SCOPE_STORE
            );

            if (count($urlRewrites) > 1 && $pathMode != Path::USE_DEFAULT) {
                $categoryRewrites = array_slice($urlRewrites, 1);

                if ($pathMode == Path::USE_SHORTEST) {
                    uasort(
                        $categoryRewrites,
                        function ($a, $b) {
                            return strlen($a) > strlen($b) ? 1 : -1;
                        }
                    );
                } else {
                    uasort(
                        $categoryRewrites,
                        function ($a, $b) {
                            return strlen($a) < strlen($b) ? 1 : -1;
                        }
                    );
                }

                $urlWithCategory = reset($categoryRewrites);
            } else {
                $categories = isset($this->rowCategories[$productId]) ? $this->rowCategories[$productId] : [];
                $lastCategoryId = count($categories) > 0 ? end($categories) : null;
                $urlWithCategory = isset($urlRewrites[$lastCategoryId])
                    ? $urlRewrites[$lastCategoryId]
                    : end($urlRewrites);
            }

            $routeParamsShort = [
                '_direct' => isset($urlRewrites[0]) ? $urlRewrites[0] : end($urlRewrites),
                '_nosid' => true,
                '_query' => array_merge((array)$this->export->getUtmParams(), ['___store' => null]),
                '_scope_to_url' => true, //as in  \Magento\Store\Model\Store::getUrl()
                '_scope' => $this->url->getScope(),
            ];
            $routeParamsWithCategory = [
                '_direct' => $urlWithCategory,
                '_nosid' => true,
                '_query' => array_merge((array)$this->export->getUtmParams(), ['___store' => null]),
                '_scope_to_url' => true, //as in  \Magento\Store\Model\Store::getUrl()
                '_scope' => $this->url->getScope(),
            ];
            $customData[ExportProduct::PREFIX_URL_ATTRIBUTE] = [
                'short' => $this->url->getUrl('', $routeParamsShort),
                'with_category' => $this->url->getUrl('', $routeParamsWithCategory),
            ];
        } else {
            $lastCategoryId = $dataRow['amasty_custom_data'][ExportProduct::PREFIX_CATEGORY_ID_ATTRIBUTE] ?? null;
            $routeParamsShort = [
                '_nosid' => true,
                '_query' => array_merge((array)$this->export->getUtmParams(), ['___store' => null]),
                '_scope_to_url' => true,
                'id' => $productId,
                's' => isset($dataRow['url_key']) ? $dataRow['url_key'] : '',
                '_scope' => $this->url->getScope(),
            ];
            $routeParamsWithCategory = array_merge($routeParamsShort, ['category' => $lastCategoryId]);
            $customData[ExportProduct::PREFIX_URL_ATTRIBUTE] = [
                'short' => $this->url->getUrl('catalog/product/view', $routeParamsShort),
                'with_category' => $this->url->getUrl('catalog/product/view', $routeParamsWithCategory)
            ];
        }

        return $dataRow;
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }
}
