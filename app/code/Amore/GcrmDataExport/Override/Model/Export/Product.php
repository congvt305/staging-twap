<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 29/9/21
 * Time: 3:36 PM
 */
namespace Amore\GcrmDataExport\Override\Model\Export;

use Amore\GcrmDataExport\Helper\Data;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as EavAttributeCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory as OptionCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\ProductFactory;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\Catalog\Model\Product as ProductEntity;
use Magento\CatalogImportExport\Model\Export\Product as MainProduct;
use Magento\CatalogImportExport\Model\Export\ProductFilterInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ImportExport\Model\Export\ConfigInterface;
use Magento\ImportExport\Model\Export\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Product
 *
 * Overrided Product class to add primary key in export file
 */
class Product extends MainProduct
{
    const XML_PATH_ACTIVE_EXTENSION = 'amore_gcrm/general/active';

    const HEADER_COLUMNS = [
        'entity_id',
        'sku',
        'store_view_code',
        'attribute_set_code',
        'product_type',
        'categories',
        'product_websites',
        'name',
        'weight',
        'product_online',
        'tax_class_name',
        'visibility',
        'price',
        'special_price',
        'special_price_from_date',
        'special_price_to_date',
        'url_key',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'base_image',
        'small_image',
        'thumbnail_image',
        'swatch_image',
        'swatch_image_label',
        'created_at',
        'updated_at',
        'new_from_date',
        'new_to_date',
        'display_product_options_in',
        'map_price',
        'msrp_price',
        'map_enabled',
        'gift_message_available',
        'custom_design',
        'custom_design_from',
        'custom_design_to',
        'custom_layout_update',
        'page_layout',
        'product_options_container',
        'msrp_display_actual_price_type',
        'country_of_manufacture',
        'additional_attributes',
        'qty',
        'out_of_stock_qty',
        'use_config_min_qty',
        'is_qty_decimal',
        'allow_backorders',
        'use_config_backorders',
        'min_cart_qty',
        'use_config_min_sale_qty',
        'max_cart_qty',
        'use_config_max_sale_qty',
        'is_in_stock',
        'notify_on_stock_below',
        'use_config_notify_stock_qty',
        'manage_stock',
        'use_config_manage_stock',
        'use_config_qty_increments',
        'qty_increments',
        'use_config_enable_qty_inc',
        'enable_qty_increments',
        'is_decimal_divided',
        'website_id',
        'deferred_stock_update',
        'use_config_deferred_stock_update',
        'related_skus',
        'related_position',
        'crosssell_skus',
        'crosssell_position',
        'upsell_skus',
        'upsell_position',
        'additional_images',
        'additional_image_labels',
        'hide_from_product_page',
        'custom_options',
        'bundle_price_type',
        'bundle_sku_type',
        'bundle_price_view',
        'bundle_weight_type',
        'bundle_values',
        'bundle_shipment_type',
        'configurable_variations',
        'configurable_variation_labels',
        'giftcard_type',
        'giftcard_allow_open_amount',
        'giftcard_open_amount_min',
        'giftcard_open_amount_max',
        'giftcard_amount',
        'use_config_is_redeemable',
        'giftcard_is_redeemable',
        'use_config_lifetime',
        'giftcard_lifetime',
        'use_config_allow_message',
        'giftcard_allow_message',
        'use_config_email_template',
        'giftcard_email_template',
        'associated_skus',
        'downloadable_links',
        'downloadable_samples'
        ];

    /**
     * Attributes defined by user
     *
     * @var array
     */
    private $userDefinedAttributes = [];

    /**#@+
     * Constants for export column.
     */
    const ENTITY_ID = 'entity_id';
    /**#@-*/

    /**
     * Columns to exclude from exported file
     *
     * @var array
     */
    protected $_excludeHeadColumns = [
        'description',
        'short_description',
        'base_image_label',
        'small_image_label',
        'thumbnail_image_label'
    ];

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var \Amore\GcrmDataExport\Model\Config\Config
     */
    private $gcrmConfig;

    /**
     * @param TimezoneInterface $localeDate
     * @param Config $config
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     * @param ConfigInterface $exportConfig
     * @param ProductFactory $productFactory
     * @param EavAttributeCollectionFactory $attrSetColFactory
     * @param CategoryCollectionFactory $categoryColFactory
     * @param ItemFactory $itemFactory
     * @param OptionCollectionFactory $optionColFactory
     * @param AttributeCollectionFactory $attributeColFactory
     * @param MainProduct\Type\Factory $_typeFactory
     * @param ProductEntity\LinkTypeProvider $linkTypeProvider
     * @param RowCustomizerInterface $rowCustomizer
     * @param DataPersistorInterface $dataPersistor
     * @param Data $dataHelper
     * @param \Amore\GcrmDataExport\Model\Config\Config $gcrmConfig
     * @param array $dateAttrCodes
     * @param ProductFilterInterface|null $filter
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        TimezoneInterface $localeDate,
        Config $config,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        ConfigInterface $exportConfig,
        ProductFactory $productFactory,
        EavAttributeCollectionFactory $attrSetColFactory,
        CategoryCollectionFactory $categoryColFactory,
        ItemFactory $itemFactory,
        OptionCollectionFactory $optionColFactory,
        AttributeCollectionFactory $attributeColFactory,
        MainProduct\Type\Factory $_typeFactory,
        ProductEntity\LinkTypeProvider $linkTypeProvider,
        RowCustomizerInterface $rowCustomizer,
        DataPersistorInterface $dataPersistor,
        Data $dataHelper,
        \Amore\GcrmDataExport\Model\Config\Config $gcrmConfig,
        array $dateAttrCodes = [],
        ?ProductFilterInterface $filter = null
    ) {
        $this->gcrmConfig = $gcrmConfig;
        parent::__construct(
            $localeDate,
            $config,
            $resource,
            $storeManager,
            $logger,
            $collectionFactory,
            $exportConfig,
            $productFactory,
            $attrSetColFactory,
            $categoryColFactory,
            $itemFactory,
            $optionColFactory,
            $attributeColFactory,
            $_typeFactory,
            $linkTypeProvider,
            $rowCustomizer,
            $dateAttrCodes,
            $filter
        );
        $this->dataHelper       = $dataHelper;
        $this->dataPersistor    = $dataPersistor;
    }

    /**
     * Return non-system attributes

     * @return array
     */
    private function getNonSystemAttributes(): array
    {
        $attrKeys = [];
        foreach ($this->filterAttributeCollection($this->getAttributeCollection()) as $attribute) {
            $attrKeys[] = $attribute->getAttributeCode();
        }

        return array_diff($this->_getExportMainAttrCodes(), $this->_customHeadersMapping($attrKeys));
    }


    /**
     * Initialize stores hash.
     *
     * @return $this
     */
    protected function _initStores()
    {
        foreach ($this->_storeManager->getStores(true) as $store) {
            //Customize get follow store config
            if ($this->gcrmConfig->getConfigValue(self::XML_PATH_ACTIVE_EXTENSION, $store->getId())) {
                // phpstan:ignore "Access to an undefined property"
                $this->_storeIdToCode[$store->getId()] = $store->getCode();
            }
        }
        // phpstan:ignore "Access to an undefined property"
        ksort($this->_storeIdToCode);
        // to ensure that 'admin' store (ID is zero) goes first

        return $this;
    }
    /**
     * Export process
     *
     * @return string
     */
    public function export()
    {
        //Execution time may be very long
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        set_time_limit(0);

        $writer = $this->getWriter();
        $page = 0;
        while (true) {
            ++$page;
            $entityCollection = $this->_getEntityCollection(true);
            $entityCollection->setOrder('entity_id', 'asc');
            $entityCollection->setStoreId(Store::DEFAULT_STORE_ID);
            $this->_prepareEntityCollection($entityCollection);
            $this->paginateCollection($page, $this->getItemsPerPage());
            if ($entityCollection->count() == 0) {
                break;
            }
            $exportData = $this->getExportData();
            if ($page == 1) {
                $writer->setHeaderCols($this->_getHeaderColumns());
            }
            foreach ($exportData as $dataRow) {
                $writer->writeRow($this->_customFieldsMapping($dataRow));
            }
            if ($entityCollection->getCurPage() >= $entityCollection->getLastPageNumber()) {
                break;
            }
        }
        return $writer->getContents();
    }

    public function _getHeaderColumns()
    {
        return self::HEADER_COLUMNS;
    }

    /**
     * Get export data for collection
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function getExportData()
    {
        $exportData = [];
        try {
            $rawData = $this->collectRawData();
            $multirawData = $this->collectMultirawData();

            $productIds = array_keys($rawData);
            $stockItemRows = $this->prepareCatalogInventory($productIds);

            $this->rowCustomizer->prepareData(
                $this->_prepareEntityCollection($this->_entityCollectionFactory->create()),
                $productIds
            );

            foreach ($rawData as $productId => $productData) {
                foreach ($productData as $storeId => $dataRow) {
                    if (isset($stockItemRows[$productId])) {
                        // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                        $dataRow = array_merge($dataRow, $stockItemRows[$productId]);
                    }
                    $this->appendMultirowData($dataRow, $multirawData);
                    if ($dataRow) {
                        $exportData[] = $dataRow;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return $exportData;
    }

    /**
     * Collect export data for all products
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    protected function collectRawData()
    {
        $data = [];
        $items = $this->loadCollection();

        /**
         * @var int $itemId
         * @var ProductEntity[] $itemByStore
         */
        foreach ($items as $itemId => $itemByStore) {
            foreach ($this->_storeIdToCode as $storeId => $storeCode) {
                $item = $itemByStore[$storeId];
                $productLinkId = $item->getData($this->getProductEntityLinkField());
                foreach ($this->_getExportAttrCodes() as $code) {
                    $attrValue = $item->getData($code);
                    if ($attrValue) {
                        //remove pesky carriage returns
                        $attrValue = preg_replace('/[\x0D][\x0A]/', '', $attrValue);
                        $attrValue = preg_replace('/[\x0D]/', '', $attrValue);
                    }
                    if (!$this->isValidAttributeValue($code, $attrValue)) {
                        continue;
                    }

                    if (isset($this->_attributeValues[$code][$attrValue]) && !empty($this->_attributeValues[$code])) {
                        $attrValue = $this->_attributeValues[$code][$attrValue];
                    }
                    $fieldName = isset($this->_fieldsMap[$code]) ? $this->_fieldsMap[$code] : $code;

                    if ($this->_attributeTypes[$code] == 'datetime') {
                        if (in_array($code, $this->dateAttrCodes)
                            || in_array($code, $this->userDefinedAttributes)
                        ) {
                            $attrValue = $this->_localeDate->formatDateTime(
                                new \DateTime($attrValue),
                                \IntlDateFormatter::SHORT,
                                \IntlDateFormatter::NONE,
                                null,
                                date_default_timezone_get()
                            );
                        } else {
                            $attrValue = $this->_localeDate->formatDateTime(
                                new \DateTime($attrValue),
                                \IntlDateFormatter::SHORT,
                                \IntlDateFormatter::SHORT
                            );
                        }
                    }

                    if ($storeId != Store::DEFAULT_STORE_ID
                        && isset($data[$itemId][Store::DEFAULT_STORE_ID][$fieldName])
                        && $data[$itemId][Store::DEFAULT_STORE_ID][$fieldName] == htmlspecialchars_decode($attrValue)
                    ) {
                        continue;
                    }

                    if ($this->_attributeTypes[$code] !== 'multiselect') {
                        if (is_scalar($attrValue)) {
                            $data[$itemId][$storeId][$fieldName] = htmlspecialchars_decode($attrValue);
                        }
                    } else {
                        $this->collectMultiselectValues($item, $code, $storeId);
                    }
                }

                unset($data[$itemId][$storeId][self::COL_ADDITIONAL_ATTRIBUTES]);
                $attrSetId = $item->getAttributeSetId();
                if ($this->dataPersistor->get('gcrm_export_check')) {
                    $data[$itemId][$storeId][self::ENTITY_ID] = $item->getId();
                }
                $data[$itemId][$storeId][self::COL_STORE] = $storeCode;
                $data[$itemId][$storeId][self::COL_ATTR_SET] = $this->_attrSetIdToName[$attrSetId];
                $data[$itemId][$storeId][self::COL_TYPE] = $item->getTypeId();
                $data[$itemId][$storeId][self::COL_SKU] = htmlspecialchars_decode($item->getSku());
                $data[$itemId][$storeId]['store_id'] = $storeId;
                $data[$itemId][$storeId]['product_id'] = $itemId;
                $data[$itemId][$storeId]['product_link_id'] = $productLinkId;
            }
        }

        return $data;
    }
    //phpcs:enable Generic.Metrics.NestingLevel

    /**
     * Wrap values with double quotes if "Fields Enclosure" option is enabled
     *
     * @param string|array $value
     * @return string|array
     */
    private function wrapValue($value)
    {
        if (!empty($this->_parameters[\Magento\ImportExport\Model\Export::FIELDS_ENCLOSURE])) {
            $wrap = function ($value) {
                return sprintf('"%s"', str_replace('"', '""', $value));
            };

            $value = is_array($value) ? array_map($wrap, $value) : $wrap($value);
        }

        return $value;
    }

    /**
     * Append multi row data
     *
     * @param array $dataRow
     * @param array $multiRawData
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function appendMultirowData(&$dataRow, $multiRawData)
    {
        $productId = $dataRow['product_id'];
        $productLinkId = $dataRow['product_link_id'];
        $storeId = $dataRow['store_id'];
        $sku = $dataRow[self::COL_SKU];
        $type = $dataRow[self::COL_TYPE];
        $attributeSet = $dataRow[self::COL_ATTR_SET];

        if ($this->dataPersistor->get('gcrm_export_check')) {
            $entityId = $dataRow[self::ENTITY_ID];
            unset($dataRow[self::ENTITY_ID]);
        }

        unset($dataRow['product_id']);
        unset($dataRow['product_link_id']);
        unset($dataRow['store_id']);
        unset($dataRow[self::COL_SKU]);
        unset($dataRow[self::COL_STORE]);
        unset($dataRow[self::COL_ATTR_SET]);
        unset($dataRow[self::COL_TYPE]);

        if (Store::DEFAULT_STORE_ID == $storeId) {
            $this->updateDataWithCategoryColumns($dataRow, $multiRawData['rowCategories'], $productId);
            if (!empty($multiRawData['rowWebsites'][$productId])) {
                $websiteCodes = [];
                foreach ($multiRawData['rowWebsites'][$productId] as $productWebsite) {
                    $websiteCodes[] = $this->_websiteIdToCode[$productWebsite];
                }
                $dataRow[self::COL_PRODUCT_WEBSITES] =
                    implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $websiteCodes);
                $multiRawData['rowWebsites'][$productId] = [];
            }
            if (!empty($multiRawData['mediaGalery'][$productLinkId])) {
                $additionalImages = [];
                $additionalImageLabels = [];
                $additionalImageIsDisabled = [];
                foreach ($multiRawData['mediaGalery'][$productLinkId] as $mediaItem) {
                    if ((int)$mediaItem['_media_store_id'] === Store::DEFAULT_STORE_ID) {
                        $additionalImages[] = $mediaItem['_media_image'];
                        $additionalImageLabels[] = $mediaItem['_media_label'];

                        if ($mediaItem['_media_is_disabled'] == true) {
                            $additionalImageIsDisabled[] = $mediaItem['_media_image'];
                        }
                    }
                }
                $dataRow['additional_images'] =
                    implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additionalImages);
                $dataRow['additional_image_labels'] =
                    implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additionalImageLabels);
                $dataRow['hide_from_product_page'] =
                    implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additionalImageIsDisabled);
                $multiRawData['mediaGalery'][$productLinkId] = [];
            }
            foreach ($this->_linkTypeProvider->getLinkTypes() as $linkTypeName => $linkId) {
                if (!empty($multiRawData['linksRows'][$productLinkId][$linkId])) {
                    $colPrefix = $linkTypeName . '_';

                    $associations = [];
                    foreach ($multiRawData['linksRows'][$productLinkId][$linkId] as $linkData) {
                        if ($linkData['default_qty'] !== null) {
                            $skuItem = $linkData['sku'] . ImportProduct::PAIR_NAME_VALUE_SEPARATOR .
                                $linkData['default_qty'];
                        } else {
                            $skuItem = $linkData['sku'];
                        }
                        $associations[$skuItem] = $linkData['position'];
                    }
                    $multiRawData['linksRows'][$productLinkId][$linkId] = [];
                    asort($associations);
                    $dataRow[$colPrefix . 'skus'] =
                        implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, array_keys($associations));
                    $dataRow[$colPrefix . 'position'] =
                        implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, array_values($associations));
                }
            }
            $dataRow = $this->rowCustomizer->addData($dataRow, $productId);
        } else {
            $additionalImageIsDisabled = [];
            if (!empty($multiRawData['mediaGalery'][$productLinkId])) {
                foreach ($multiRawData['mediaGalery'][$productLinkId] as $mediaItem) {
                    if ((int)$mediaItem['_media_store_id'] === $storeId) {
                        if ($mediaItem['_media_is_disabled'] == true) {
                            $additionalImageIsDisabled[] = $mediaItem['_media_image'];
                        }
                    }
                }
            }
            if ($additionalImageIsDisabled) {
                $dataRow['hide_from_product_page'] =
                    implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additionalImageIsDisabled);
            }
        }

        if (!empty($this->collectedMultiselectsData[$storeId][$productId])) {
            foreach (array_keys($this->collectedMultiselectsData[$storeId][$productId]) as $attrKey) {
                if (!empty($this->collectedMultiselectsData[$storeId][$productId][$attrKey])) {
                    $dataRow[$attrKey] = implode(
                        Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
                        $this->collectedMultiselectsData[$storeId][$productId][$attrKey]
                    );
                }
            }
        }

        if (!empty($multiRawData['customOptionsData'][$productLinkId][$storeId])) {
            $shouldBeMerged = true;
            $customOptionsRows = $multiRawData['customOptionsData'][$productLinkId][$storeId];

            if ($storeId != Store::DEFAULT_STORE_ID
                && !empty($multiRawData['customOptionsData'][$productLinkId][Store::DEFAULT_STORE_ID])
            ) {
                $defaultCustomOptions = $multiRawData['customOptionsData'][$productLinkId][Store::DEFAULT_STORE_ID];
                if (!array_diff($defaultCustomOptions, $customOptionsRows)) {
                    $shouldBeMerged = false;
                }
            }

            if ($shouldBeMerged) {
                $multiRawData['customOptionsData'][$productLinkId][$storeId] = [];
                $customOptions = implode(ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR, $customOptionsRows);
                $dataRow = array_merge($dataRow, ['custom_options' => $customOptions]);
            }
        }

        if (empty($dataRow)) {
            return null;
        } elseif ($storeId != Store::DEFAULT_STORE_ID) {
            $dataRow[self::COL_STORE] = $this->_storeIdToCode[$storeId];
        }

        if ($this->dataPersistor->get('gcrm_export_check')) {
            $dataRow[self::ENTITY_ID] = $entityId;
        }

        $dataRow[self::COL_SKU] = $sku;
        $dataRow[self::COL_ATTR_SET] = $attributeSet;
        $dataRow[self::COL_TYPE] = $type;

        return $dataRow;
    }
}
