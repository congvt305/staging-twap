<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 29/6/21
 * Time: 12:55 PM
 */
namespace Amore\GcrmDataExport\Model\Export\Quote;

use Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory as ExportCollection;
use Amore\GcrmDataExport\Model\Export\Quote\AttributeCollectionProvider;
use Amore\GcrmDataExport\Model\Export\Adapter\QuoteCsv;
use Amore\GcrmDataExport\Model\QuoteColumnsInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Data\Collection as CollectionAlias;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Eav\Model\Config;
use Magento\ImportExport\Model\Export\Entity\AbstractEav;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote as QuoteAlias;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\FileSystemException;

/**
 * This class Exports Quotes
 *
 * Class Quote
 */
class Quote extends AbstractEntity implements QuoteColumnsInterface
{
    /**#@+
     * Constants for Quote Attributes.
     */
    protected $headColumnNames = [
        self::QUOTE_ENTITY_ID => 'entity_id',
        self::QUOTE_STORE_ID => 'store_id',
        self::QUOTE_CREATED_AT => 'created_at',
        self::QUOTE_UPDATED_AT => 'updated_at',
        self::QUOTE_CONVERTED_AT => 'converted_at',
        self::QUOTE_IS_ACTIVE => 'is_active',
        self::QUOTE_IS_VIRTUAL => 'is_virtual',
        self::QUOTE_IS_MULTI_SHIPPING => 'is_multi_shipping',
        self::QUOTE_ITEMS_COUNT => 'items_count',
        self::QUOTE_ITEMS_QTY => 'items_qty',
        self::QUOTE_ORIG_ORDER_ID => 'orig_order_id',
        self::QUOTE_STORE_TO_BASE_RATE => 'store_to_base_rate',
        self::QUOTE_STORE_TO_QUOTE_RATE => 'store_to_quote_rate',
        self::QUOTE_BASE_CURRENCY_CODE => 'base_currency_code',
        self::QUOTE_STORE_CURRENCY_CODE => 'store_currency_code',
        self::QUOTE_GRAND_TOTAL => 'grand_total',
        self::QUOTE_BASE_GRAND_TOTAL => 'base_grand_total',
        self::QUOTE_CHECKOUT_METHOD => 'checkout_method',
        self::QUOTE_CUSTOMER_ID => 'customer_id',
        self::QUOTE_CUSTOMER_TAX_CLASS_ID => 'customer_tax_class_id',
        self::QUOTE_CUSTOMER_GROUP_ID => 'customer_group_id',
        self::QUOTE_CUSTOMER_EMAIL => 'customer_email',
        self::QUOTE_CUSTOMER_PREFIX => 'customer_prefix',
        self::QUOTE_CUSTOMER_FIRSTNAME => 'customer_firstname',
        self::QUOTE_CUSTOMER_MIDDLENAME => 'customer_middlename',
        self::QUOTE_CUSTOMER_LASTNAME => 'customer_lastname',
        self::QUOTE_CUSTOMER_SUFFIX => 'customer_suffix',
        self::QUOTE_CUSTOMER_DOB => 'customer_dob',
        self::QUOTE_CUSTOMER_NOTE => 'customer_note',
        self::QUOTE_CUSTOMER_NOTE_NOTIFY => 'customer_note_notify',
        self::QUOTE_CUSTOMER_IS_GUEST => 'customer_is_guest',
        self::QUOTE_REMOTE_IP => 'remote_ip',
        self::QUOTE_APPLIED_RULE_IDS => 'applied_rule_ids',
        self::QUOTE_RESERVED_ORDER_ID => 'reserved_order_id',
        self::QUOTE_PASSWORD_HASH => 'password_hash',
        self::QUOTE_COUPON_CODE => 'coupon_code',
        self::QUOTE_GLOBAL_CURRENCY_CODE => 'global_currency_code',
        self::QUOTE_BASE_TO_GLOBAL_RATE => 'base_to_global_rate',
        self::QUOTE_BASE_TO_QUOTE_RATE => 'base_to_quote_rate',
        self::QUOTE_CUSTOMER_TAXVAT => 'customer_taxvat',
        self::QUOTE_CUSTOMER_GENDER => 'customer_gender',
        self::QUOTE_SUBTOTAL => 'subtotal',
        self::QUOTE_BASE_SUBTOTAL => 'base_subtotal',
        self::QUOTE_SUBTOTAL_WITH_DSCOUNT => 'subtotal_with_discount',
        self::QUOTE_BASE_SUBTOTAL_WITH_DSCOUNT => 'base_subtotal_with_discount',
        self::QUOTE_IS_CHANGED => 'is_changed',
        self::QUOTE_TRIGGER_RECOLLECT => 'trigger_recollect',
        self::QUOTE_EXT_SHIPPING_INFO => 'ext_shipping_info',
        self::QUOTE_CUSTOMER_BALANCE_AMOUNT => 'customer_balance_amount_used',
        self::QUOTE_BASE_CUSTOMER_BAL_AMOUNT => 'base_customer_bal_amount_used',
        self::QUOTE_USE_CUSTOMER_BALANCE => 'use_customer_balance',
        self::QUOTE_GIFT_CARDS => 'gift_cards',
        self::QUOTE_GIFT_CARDS_AMOUNT => 'gift_cards_amount',
        self::QUOTE_BASE_GIFT_CARDS_AMOUNT => 'base_gift_cards_amount',
        self::QUOTE_GIFT_CARDS_AMOUNT_USED => 'gift_cards_amount_used',
        self::QUOTE_BASE_GIFT_CARDS_AMOUNT_USED => 'base_gift_cards_amount_used',
        self::QUOTE_GIFT_MESSAGE_ID => 'gift_message_id',
        self::QUOTE_GW_ID => 'gw_id',
        self::QUOTE_GW_ALLOW_GIFT_RECEIPT => 'gw_allow_gift_receipt',
        self::QUOTE_GW_ADD_CARD => 'gw_add_card',
        self::QUOTE_GW_BASE_PRICE => 'gw_base_price',
        self::QUOTE_GW_PRICE => 'gw_price',
        self::QUOTE_GW_ITEMS_BASE_PRICE => 'gw_items_base_price',
        self::QUOTE_GW_ITEMS_PRICE => 'gw_items_price',
        self::QUOTE_GW_CARD_BASE_PRICE => 'gw_card_base_price',
        self::QUOTE_GW_CARD_PRICE => 'gw_card_price',
        self::QUOTE_GW_BASE_TAX_AMOUNT => 'gw_base_tax_amount',
        self::QUOTE_GW_TAX_AMOUNT => 'gw_tax_amount',
        self::QUOTE_GW_ITEMS_BASE_TAX_AMOUNT => 'gw_items_base_tax_amount',
        self::QUOTE_GW_ITEMS_TAX_AMOUNT => 'gw_items_tax_amount',
        self::QUOTE_GW_CARD_BASE_TAX_AMOUNT => 'gw_card_base_tax_amount',
        self::QUOTE_GW_CARD_TAX_AMOUNT => 'gw_card_tax_amount',
        self::QUOTE_GW_BASE_PRICE_INCL_TAX => 'gw_base_price_incl_tax',
        self::QUOTE_GW_PRICE_INCL_TAX => 'gw_price_incl_tax',
        self::QUOTE_GW_ITEMS_BASE_PRICE_INCL_TAX => 'gw_items_base_price_incl_tax',
        self::QUOTE_GW_ITEMS_PRICE_INCL_TAX => 'gw_items_price_incl_tax',
        self::QUOTE_GW_CARD_BASE_PRICE_INCL_TAX => 'gw_card_base_price_incl_tax',
        self::QUOTE_GW_CARD_PRICE_INCL_TAX => 'gw_card_price_incl_tax',
        self::QUOTE_IS_PERSISTENT => 'is_persistent',
        self::QUOTE_USE_REWARD_POINTS => 'use_reward_points',
        self::QUOTE_REWARD_POINTS_BALANCE => 'reward_points_balance',
        self::QUOTE_BASE_REWARD_CURRENCY_AMOUNT => 'base_reward_currency_amount',
        self::QUOTE_REWARD_CURRENCY_AMOUNT => 'reward_currency_amount',
        self::QUOTE_DELIVERY_MESSAGE => 'delivery_message',
        self::QUOTE_PAYPAL_ORDER_SUBTOTAL => 'paypal_subtotal',
        self::QUOTE_PAYPAL_GRAND_TOTAL => 'paypal_grand_total',
        self::QUOTE_PAYPAL_TAX_AMOUNT => 'paypal_tax_amount',
        self::QUOTE_PAYPAL_SHIPPING_AMOUNT => 'paypal_shipping_amount',
        self::QUOTE_PAYPAL_DISCOUNT_AMOUNT => 'paypal_discount_amount',
        self::QUOTE_PAYPAL_RATE => 'paypal_rate',
        self::QUOTE_PAYPAL_CURRENCY_CODE => 'paypal_currency_code',
    ];
    /**#@-*/

    /**
     * @var QuoteCsv
     */
    private $quoteWriter;

    /**
     * @var CollectionFactory
     */
    private $quoteColFactory;

    /**
     * @var AttributeCollectionProvider
     */
    private $attributeCollectionProvider;

    /**
     * @var Collection
     */
    private $quoteCollection;

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

    /**
     * Quote constructor.
     * @param ExportCollection $ExportCollectionFactory
     * @param AttributeCollectionProvider $attributeCollectionProvider
     * @param CollectionFactory $quoteColFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param QuoteCsv $quoteWriter
     * @param Factory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     * @param array $data
     */
    public function __construct(
        ExportCollection $ExportCollectionFactory,
        AttributeCollectionProvider $attributeCollectionProvider,
        CollectionFactory $quoteColFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        QuoteCsv $quoteWriter,
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
        $this->quoteColFactory = $quoteColFactory;
        $this->attributeCollectionProvider = $attributeCollectionProvider;
        $this->quoteWriter = $quoteWriter;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Main Export Function
     *
     * @return string
     * @throws LocalizedException
     * @throws FileSystemException
     */
    public function export()
    {
        $writer = $this->getQuoteWriter();

        $engHeader = $this->_getHeaderColumns();

        $writer->setHeaderCols($engHeader);

        $quoteData = $this->getItemData();
        if ($quoteData == null) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('There is no data for the export.'));
            return $resultRedirect->setPath('*/*/index');
        }

        foreach ($quoteData as $quotes) {
            foreach ($quotes as $quote) {
                $writer->writeSourceRowWithCustomColumns($quote, $engHeader);
            }
        }
        return $writer->getContents();
    }

    /**
     * Return Data of Quotes
     *
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
     *
     * @param Order $item
     * @return void
     */
    public function exportItem($item)
    {
        // TODO: Implement exportItem() method.
    }

    /**
     * This method is used to get the entity type
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return "quote";
    }

    /**
     * This method is used to get the get header columns
     *
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

    /**
     * This function gets Entity Collection
     *
     * @return AbstractDb|void
     */
    protected function _getEntityCollection()
    {
        // will not implement this method as it is legacy interface
    }

    /**
     * This method is used to get the attribute values
     *
     * @return CollectionAlias
     * @throws \Exception
     */
    public function getAttributeCollection()
    {
        return $this->attributeCollectionProvider->get();
    }

    /**
     * joinedItemCollection
     *
     * @return CollectionFactory
     */
    public function joinedItemCollection()
    {
        try {
            $customExportData = $this->ExportCollectionFactory->create()
                ->addFieldToFilter('entity_code', ['eq' => 'quote'])->getFirstItem();
            $exportDate = $customExportData->getData('updated_at');
            if ($exportDate == "NULL") {
                /** @var CollectionFactory $collection */
                $collection = $this->quoteColFactory->create();
            } else {
                /** @var CollectionFactory $collection */
                $collection = $this->quoteColFactory->create();
                $collection->addFieldToFilter('updated_at', ['gteq' => $exportDate]);
            }
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $collection;
    }

    /**
     * Get Quote Writer for CSV File
     *
     * @return OrderItemsCsv
     * @throws LocalizedException
     */
    public function getQuoteWriter()
    {
        if (!$this->quoteWriter) {
            throw new LocalizedException(__('Please specify the order items writer.'));
        }
        return $this->quoteWriter;
    }

    /**
     * Set Quote Writer for CSV File
     *
     * @param QuoteCsv $quoteWriter
     * @return $this
     */
    public function setQuoteWriter(QuoteCsv $quoteWriter)
    {
        $this->quoteWriter = $quoteWriter;
        return $this;
    }
}
