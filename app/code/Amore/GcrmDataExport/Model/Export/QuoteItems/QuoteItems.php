<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 29/6/21
 * Time: 12:55 PM
 */
namespace Amore\GcrmDataExport\Model\Export\QuoteItems;

use Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory as ExportCollection;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Amore\GcrmDataExport\Model\Export\Adapter\QuoteItemsCsv;
use Magento\Framework\Data\Collection as CollectionAlias;
use Amore\GcrmDataExport\Model\Export\QuoteItems\AttributeCollectionProvider;
use Amore\GcrmDataExport\Model\QuoteItemsColumnsInterface;
use Magento\Quote\Model\Quote\Item as QuoteItemsAlias;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Eav\Model\Config;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\ImportExport\Model\Export\Entity\AbstractEav;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\FileSystemException;

/**
 * Export Sales Order
 */
class QuoteItems extends AbstractEntity implements QuoteItemsColumnsInterface
{
    protected $headColumnNames = [
        self::QUOTE_ITEM_ITEM_ID => 'item_id',
        self::QUOTE_ITEM_QUOTE_ID => 'quote_id',
        self::QUOTE_ITEM_CREATED_AT => 'created_at',
        self::QUOTE_ITEM_UPDATED_AT => 'updated_at',
        self::QUOTE_ITEM_PRODUCT_ID => 'product_id',
        self::QUOTE_ITEM_STORE_ID => 'store_id',
        self::QUOTE_ITEM_PARENT_ITEM_ID => 'parent_item_id',
        self::QUOTE_ITEM_IS_VIRTUAL => 'is_virtual',
        self::QUOTE_ITEM_SKU => 'sku',
        self::QUOTE_ITEM_NAME => 'name',
        self::QUOTE_ITEM_DESCRIPTION => 'description',
        self::QUOTE_ITEM_APPLIED_RULE_IDS => 'applied_rule_ids',
        self::QUOTE_ITEM_ADDITIONAL_DATA => 'additional_data',
        self::QUOTE_ITEM_IS_QTY_DECIMAL => 'is_qty_decimal',
        self::QUOTE_ITEM_NO_DISCOUNT => 'no_discount',
        self::QUOTE_ITEM_WEIGHT => 'weight',
        self::QUOTE_ITEM_QTY => 'qty',
        self::QUOTE_ITEM_PRICE => 'price',
        self::QUOTE_ITEM_BASE_PRICE => 'base_price',
        self::QUOTE_ITEM_CUSTOM_PRICE => 'custom_price',
        self::QUOTE_ITEM_DISCOUNT_PERCENT => 'discount_percent',
        self::QUOTE_ITEM_DISCOUNT_AMOUNT => 'discount_amount',
        self::QUOTE_ITEM_BASE_DISCOUNT_AMOUNT => 'base_discount_amount',
        self::QUOTE_ITEM_TAX_PERCENT => 'tax_percent',
        self::QUOTE_ITEM_TAX_AMOUNT => 'tax_amount',
        self::QUOTE_ITEM_BASE_TAX_AMOUNT => 'base_tax_amount',
        self::QUOTE_ITEM_ROW_TOTAL => 'row_total',
        self::QUOTE_ITEM_BASE_ROW_TOTAL => 'base_row_total',
        self::QUOTE_ITEM_ROW_TOTAL_WITH_DISCOUNT => 'row_total_with_discount',
        self::QUOTE_ITEM_ROW_WEIGHT => 'row_weight',
        self::QUOTE_ITEM_PRODUCT_TYPE => 'product_type',
        self::QUOTE_ITEM_BASE_TAX_BEFORE_DISCOUNT => 'base_tax_before_discount',
        self::QUOTE_ITEM_TAX_BEFORE_DISCOUNT => 'tax_before_discount',
        self::QUOTE_ITEM_ORIGINAL_CUSTOM_PRICE => 'original_custom_price',
        self::QUOTE_ITEM_REDIRECT_URL => 'redirect_url',
        self::QUOTE_ITEM_BASE_COST => 'base_cost',
        self::QUOTE_ITEM_PRICE_INCL_TAX => 'price_incl_tax',
        self::QUOTE_ITEM_BASE_PRICE_INCL_TAX => 'base_price_incl_tax',
        self::QUOTE_ITEM_ROW_TOTAL_INCL_TAX => 'row_total_incl_tax',
        self::QUOTE_ITEM_BASE_ROW_TOTAL_INCL_TAX => 'base_row_total_incl_tax',
        self::QUOTE_ITEM_DISCOUNT_TAX_COMPENSATION_AMOUNT => 'discount_tax_compensation_amount',
        self::QUOTE_ITEM_BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT => 'base_discount_tax_compensation_amount',
        self::QUOTE_ITEM_EVENT_ID => 'event_id',
        self::QUOTE_ITEM_GIFT_MESSAGE_ID => 'gift_message_id',
        self::QUOTE_ITEM_GW_ID => 'gw_id',
        self::QUOTE_ITEM_GW_BASE_PRICE => 'gw_base_price',
        self::QUOTE_ITEM_GW_PRICE => 'gw_price',
        self::QUOTE_ITEM_GW_BASE_TAX_AMOUNT => 'gw_base_tax_amount',
        self::QUOTE_ITEM_GW_TAX_AMOUNT => 'gw_tax_amount',
        self::QUOTE_ITEM_WEEE_TAX_APPLIED => 'weee_tax_applied',
        self::QUOTE_ITEM_WEEE_TAX_APPLIED_AMOUNT => 'weee_tax_applied_amount',
        self::QUOTE_ITEM_WEEE_TAX_APPLIED_ROW_AMOUNT => 'weee_tax_applied_row_amount',
        self::QUOTE_ITEM_WEEE_TAX_DISPOSITION => 'weee_tax_disposition',
        self::QUOTE_ITEM_WEEE_TAX_ROW_DISPOSITION => 'weee_tax_row_disposition',
        self::QUOTE_ITEM_BASE_WEEE_TAX_APPLIED_AMOUNT => 'base_weee_tax_applied_amount',
        self::QUOTE_ITEM_BASE_WEEE_TAX_APPLIED_ROW_AMOUNT => 'base_weee_tax_applied_row_amnt',
        self::QUOTE_ITEM_BASE_WEEE_TAX_DISPOSITION => 'base_weee_tax_disposition',
        self::QUOTE_ITEM_BASE_WEEE_TAX_ROW_DISPOSITION => 'base_weee_tax_row_disposition',
        self::QUOTE_ITEM_FREE_SHIPPING => 'free_shipping',
        self::QUOTE_ITEM_GIFTREGISTRY_ITEM_ID => 'giftregistery_item_id',
        self::QUOTE_ITEM_PAYPAL_PRICE => 'paypal_price',
        self::QUOTE_ITEM_PAYPAL_ROW_TOATL => 'paypal_row_total',
    ];

    /**
     * @var QuoteItemsCsv
     */
    private $quoteItemsWriter;

    /**
     * @var CollectionFactory
     */
    private $quoteItemsColFactory;

    /**
     * @var AttributeCollectionProvider
     */
    private $attributeCollectionProvider;

    /**
     * @var Collection
     */
    private $quoteItemsCollection;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ExportCollection
     */
    private $ExportCollectionFactory;

    /** Attribute collection name.
     * Used to resolve entity attribute collection.
     */
    const ATTRIBUTE_COLLECTION_NAME = Collection::class;

    public function __construct(
        ExportCollection $ExportCollectionFactory,
        AttributeCollectionProvider $attributeCollectionProvider,
        CollectionFactory $quoteItemsColFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        QuoteItemsCsv $quoteItemsWriter,
        Factory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        array $data = []
    ) {
        parent::__construct(
            $scopeConfig,
            $storeManager,
            $collectionFactory,
            $resourceColFactory,
            $data
        );
        $this->ExportCollectionFactory = $ExportCollectionFactory;
        $this->quoteItemsColFactory = $quoteItemsColFactory;
        $this->attributeCollectionProvider = $attributeCollectionProvider;
        $this->quoteItemsWriter = $quoteItemsWriter;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Main Export Function
     * @return string
     * @throws LocalizedException
     * @throws FileSystemException
     */
    public function export()
    {
        $writer = $this->getQuoteItemsWriter();

        $engHeader = $this->_getHeaderColumns();

        $writer->setHeaderCols($engHeader);

        $quoteItemsData = $this->getItemData();
        if ($quoteItemsData == null) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('There is no data for the export.'));
            return $resultRedirect->setPath('*/*/index');
        }
        foreach ($quoteItemsData as $quoteItems) {
            foreach ($quoteItems as $item) {
                $writer->writeSourceRowWithCustomColumns($item, $engHeader);
            }
        }
        return $writer->getContents();
    }

    /**
     * Return Data of Quote Items
     * @return array
     */
    public function getItemData()
    {
        $itemRow = [];
        $collection = $this->joinedItemCollection();
        $cnt = 0;
        foreach ($collection as $item) {
            $itemRow[$item->getIncrementId()][$cnt] = $item->getData();
            $cnt++;
        }
        return $itemRow;
    }

    /**
     * Export given Quote data
     * @param QuoteItemsAlias $item
     * @return void
     */
    public function exportItem($item)
    {
        // TODO: Implement exportItem() method.
    }

    /**
     * This method is used to get the entity type
     * @return string
     */
    public function getEntityTypeCode()
    {
        return "quote_item";
    }

    /**
     * This method is used to get the get header columns
     * @return array
     */
    protected function _getHeaderColumns()
    {
        $header = [];
        foreach ($this->headColumnNames as $englishColumn) {
            $header[] = $englishColumn;
        }
        return $header;
    }

    protected function _getEntityCollection()
    {
        // will not implement this method as it is legacy interface
    }

    /**
     * This method is used to get the attribute values
     * @return CollectionAlias
     * @throws \Exception
     */
    public function getAttributeCollection()
    {
        return $this->attributeCollectionProvider->get();
    }

    /**
     * joinedItemCollection
     * @return CollectionFactory
     */
    public function joinedItemCollection()
    {
        try {
            $customExportData = $this->ExportCollectionFactory->create()
                ->addFieldToFilter('entity_code', ['eq' => 'quote_item'])->getFirstItem();
            $exportDate = $customExportData->getData('updated_at');
            if ($exportDate == "NULL") {
                /** @var CollectionFactory $collection */
                $collection = $this->quoteItemsColFactory->create();
            } else {
                /** @var CollectionFactory $collection */
                $collection = $this->quoteItemsColFactory->create();
                $collection->addFieldToFilter('updated_at', ['gteq' => $exportDate]);
            }
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $collection;
    }

    /**
     * Get Quote Items Writer For CSV File
     * @return QuoteItemsCsv
     * @throws LocalizedException
     */
    public function getQuoteItemsWriter()
    {
        if (!$this->quoteItemsWriter) {
            throw new LocalizedException(__('Please specify the order items writer.'));
        }
        return $this->quoteItemsWriter;
    }

    /**
     * Set Quote Items Writer For CSV File
     * @param QuoteItemsCsv $quoteItemsWriter
     * @return $this
     */
    public function setQuoteItemsWriter(QuoteItemsCsv $quoteItemsWriter)
    {
        $this->quoteItemsWriter = $quoteItemsWriter;
        return $this;
    }
}
