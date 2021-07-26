<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 29/6/21
 * Time: 12:55 PM
 */
namespace Amore\GcrmDataExport\Model\Export\OrderItems;

use Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory as ExportCollection;
use Amore\GcrmDataExport\Model\Export\Adapter\OrderItemsCsv;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Collection as CollectionAlias;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;
use Amore\GcrmDataExport\Model\Export\Order\AttributeCollectionProvider;
use Amore\GcrmDataExport\Model\OrderItemsColumnsInterface;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Eav\Model\Config;
use Magento\ImportExport\Model\Export\Entity\AbstractEav;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Export Sales Order
 */
class OrderItems extends AbstractEntity implements OrderItemsColumnsInterface
{
    protected $headColumnNames = [
        self::ORDER_ITEM_ITEM_ID => 'item_id',
        self::ORDER_ITEM_ORDER_ID => 'order_id',
        self::ORDER_ITEM_PARENT_ITEM_ID => 'parent_item_id',
        self::ORDER_ITEM_QUOTE_ITEM_ID => 'quote_item_id',
        self::ORDER_ITEM_STORE_ID => 'store_id',
        self::ORDER_ITEM_CREATED_AT => 'created_at',
        self::ORDER_ITEM_UPDATED_AT => 'updated_at',
        self::ORDER_ITEM_PRODUCT_ID => 'product_id',
        self::ORDER_ITEM_PRODUCT_TYPE => 'product_type',
        self::ORDER_ITEM_WEIGHT => 'weight',
        self::ORDER_ITEM_IS_VIRTUAL => 'is_virtual',
        self::ORDER_ITEM_PRODUCT_SKU => 'sku',
        self::ORDER_ITEM_PRODUCT_NAME => 'name',
        self::ORDER_ITEM_DESCRIPTION => 'description',
        self::ORDER_ITEM_APPLIED_RULE_IDS => 'applied_rule_ids',
        self::ORDER_ITEM_ADDITIONAL_DATA => 'additional_data',
        self::ORDER_ITEM_IS_QTY_DECIMAL => 'is_qty_decimal',
        self::ORDER_ITEM_NO_DISCOUNT => 'no_discount',
        self::ORDER_ITEM_QTY_BACKORDERED => 'qty_backordered',
        self::ORDER_ITEM_QTY_CANCELED => 'qty_canceled',
        self::ORDER_ITEM_QTY_INVOICED => 'qty_invoiced',
        self::ORDER_ITEM_QTY_ORDERED => 'qty_ordered',
        self::ORDER_ITEM_QTY_REFUNDED => 'qty_refunded',
        self::ORDER_ITEM_QTY_SHIPPED => 'qty_shipped',
        self::ORDER_ITEM_BASE_COST => 'base_cost',
        self::ORDER_ITEM_PRICE => 'price',
        self::ORDER_ITEM_BASE_PRICE => 'base_price',
        self::ORDER_ITEM_ORIGINAL_PRICE => 'original_price',
        self::ORDER_ITEM_BASE_ORIGINAL_PRICE => 'base_original_price',
        self::ORDER_ITEM_TAX_PERCENT => 'tax_percent',
        self::ORDER_ITEM_TAX_AMOUNT => 'tax_amount',
        self::ORDER_ITEM_BASE_TAX_AMOUNT => 'base_tax_amount',
        self::ORDER_ITEM_TAX_INVOICED => 'tax_invoiced',
        self::ORDER_ITEM_BASE_TAX_INVOICED => 'base_tax_invoiced',
        self::ORDER_ITEM_DISCOUNT_PERCENT => 'discount_percent',
        self::ORDER_ITEM_DISCOUNT_AMOUNT => 'discount_amount',
        self::ORDER_ITEM_BASE_DISCOUNT_AMOUNT => 'base_discount_amount',
        self::ORDER_ITEM_DISCOUNT_INVOICED => 'discount_invoiced',
        self::ORDER_ITEM_BASE_DISCOUNT_INVOICED => 'base_discount_invoiced',
        self::ORDER_ITEM_AMOUNT_REFUNDED => 'amount_refunded',
        self::ORDER_ITEM_BASE_AMOUNT_REFUNDED => 'base_amount_refunded',
        self::ORDER_ITEM_ROW_TOTAL => 'row_total',
        self::ORDER_ITEM_BASE_ROW_TOTAL => 'base_row_total',
        self::ORDER_ITEM_ROW_INVOICED => 'row_invoiced',
        self::ORDER_ITEM_BASE_ROW_INVOICED => 'base_row_invoiced',
        self::ORDER_ITEM_ROW_WEIGHT => 'row_weight',
        self::ORDER_ITEM_BASE_TAX_BEFORE_DISCOUNT => 'base_tax_before_discount',
        self::ORDER_ITEM_TAX_BEFORE_DISCOUNT => 'tax_before_discount',
        self::ORDER_ITEM_EXT_ORDER_ITEM_ID => 'ext_order_item_id',
        self::ORDER_ITEM_LOCKED_DO_INVOICE => 'locked_do_invoice',
        self::ORDER_ITEM_LOCKED_DO_SHIP => 'locked_do_ship',
        self::ORDER_ITEM_PRICE_INCL_TAX => 'price_incl_tax',
        self::ORDER_ITEM_BASE_PRICE_INCL_TAX => 'base_price_incl_tax',
        self::ORDER_ITEM_ROW_TOTAL_INCL_TAX => 'row_total_incl_tax',
        self::ORDER_ITEM_BASE_ROW_TOTAL_INCL_TAX => 'base_row_total_incl_tax',
        self::ORDER_ITEM_DISCOUNT_TAX_COMPENSATION_AMOUNT => 'discount_tax_compensation_amount',
        self::ORDER_ITEM_BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT => 'base_discount_tax_compensation_amount',
        self::ORDER_ITEM_DISCOUNT_TAX_COMPENSATION_INVOICED => 'discount_tax_compensation_invoiced',
        self::ORDER_ITEM_BASE_DISCOUNT_TAX_COMPENSATION_INVOICED => 'base_discount_tax_compensation_invoiced',
        self::ORDER_ITEM_DISCOUNT_TAX_COMPENSATION_REFUNDED => 'discount_tax_compensation_refunded',
        self::ORDER_ITEM_BASE_DISCOUNT_TAX_COMPENSATION_REFUNDED => 'base_discount_tax_compensation_refunded',
        self::ORDER_ITEM_TAX_CANCELED => 'tax_canceled',
        self::ORDER_ITEM_DISCOUNT_TAX_COMPENSATION_CANCELED => 'discount_tax_compensation_canceled',
        self::ORDER_ITEM_TAX_REFUNDED => 'tax_refunded',
        self::ORDER_ITEM_BASE_TAX_REFUNDED => 'base_tax_refunded',
        self::ORDER_ITEM_DISCOUNT_REFUNDED => 'discount_refunded',
        self::ORDER_ITEM_BASE_DISCOUNT_REFUNDED => 'base_discount_refunded',
        self::ORDER_ITEM_EVENT_ID => 'event_id',
        self::ORDER_ITEM_GIFT_MESSAGE_ID => 'gift_message_id',
        self::ORDER_ITEM_GIFT_MESSAGE_AVAILABLE => 'gift_message_available',
        self::ORDER_ITEM_GW_ID => 'gw_id',
        self::ORDER_ITEM_GW_BASE_PRICE => 'gw_base_price',
        self::ORDER_ITEM_GW_PRICE => 'gw_price',
        self::ORDER_ITEM_GW_BASE_TAX_AMOUNT => 'gw_base_tax_amount',
        self::ORDER_ITEM_GW_TAX_AMOUNT => 'gw_tax_amount',
        self::ORDER_ITEM_GW_BASE_PRICE_INVOICED => 'gw_base_price_invoiced',
        self::ORDER_ITEM_GW_PRICE_INVOICED => 'gw_price_invoiced',
        self::ORDER_ITEM_GW_BASE_TAX_AMOUNT_INVOICED => 'gw_base_tax_amount_invoiced',
        self::ORDER_ITEM_GW_TAX_AMOUNT_INVOICED => 'gw_tax_amount_invoiced',
        self::ORDER_ITEM_GW_BASE_PRICE_REFUNDED => 'gw_base_price_refunded',
        self::ORDER_ITEM_GW_PRICE_REFUNDED => 'gw_price_refunded',
        self::ORDER_ITEM_GW_BASE_TAX_AMOUNT_REFUNDED => 'gw_base_tax_amount_refunded',
        self::ORDER_ITEM_GW_TAX_AMOUNT_REFUNDED => 'gw_tax_amount_refunded',
        self::ORDER_ITEM_WEEE_TAX_APPLIED => 'weee_tax_applied',
        self::ORDER_ITEM_WEEE_TAX_APPLIED_AMOUNT => 'weee_tax_applied_amount',
        self::ORDER_ITEM_WEEE_TAX_APPLIED_ROW_AMOUNT => 'weee_tax_applied_row_amount',
        self::ORDER_ITEM_WEEE_TAX_DISPOSITION => 'weee_tax_disposition',
        self::ORDER_ITEM_BASE_WEEE_TAX_APPLIED_AMOUNT => 'base_weee_tax_applied_amount',
        self::ORDER_ITEM_BASE_WEEE_TAX_APPLIED_ROW_AMOUNT => 'base_weee_tax_applied_row_amnt',
        self::ORDER_ITEM_BASE_WEEE_TAX_DISPOSITION => 'base_weee_tax_disposition',
        self::ORDER_ITEM_WEEE_TAX_ROW_DISPOSITION => 'weee_tax_row_disposition',
        self::ORDER_ITEM_FREE_SHIPPING => 'free_shipping',
        self::ORDER_ITEM_GIFTREGISTRY_ITEM_ID => 'giftregistry_item_id',
        self::ORDER_ITEM_QTY_RETURNED => 'qty_returned',
        self::ORDER_ITEM_PAYPAL_PRICE => 'paypal_price',
        self::ORDER_ITEM_PAYPAL_ROW_TOATL => 'paypal_row_total',
        self::ORDER_ITEM_SAP_ITEM_NSAMT => 'sap_item_nsamt',
        self::ORDER_ITEM_SAP_ITEM_DCAMT => 'sap_item_dcamt',
        self::ORDER_ITEM_SAP_ITEM_SLAMT => 'sap_item_slamt',
        self::ORDER_ITEM_SAP_ITEM_NETWR => 'sap_item_netwr',
    ];

    /**
     * @var OrderItemsCsv
     */
    private $orderItemsWriter;

    /**
     * @var CollectionFactory
     */
    private $orderItemsColFactory;

    /**
     * @var AttributeCollectionProvider
     */
    private $attributeCollectionProvider;

    /**
     * @var Collection
     */
    private $orderItemsCollection;

    /**
     * @var ExportCollection
     */
    private $ExportCollectionFactory;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /** Attribute collection name.
     * Used to resolve entity attribute collection.
     */
    const ATTRIBUTE_COLLECTION_NAME = Collection::class;

    /**
     * OrderItems constructor.
     * @param ExportCollection $ExportCollectionFactory
     * @param AttributeCollectionProvider $attributeCollectionProvider
     * @param CollectionFactory $orderItemsColFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param OrderItemsCsv $orderItemsWriter
     * @param Factory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param ManagerInterface $messageManager
     * @param array $data
     */
    public function __construct(
        ExportCollection $ExportCollectionFactory,
        AttributeCollectionProvider $attributeCollectionProvider,
        CollectionFactory $orderItemsColFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        OrderItemsCsv $orderItemsWriter,
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
        $this->orderItemsColFactory = $orderItemsColFactory;
        $this->attributeCollectionProvider = $attributeCollectionProvider;
        $this->orderItemsWriter = $orderItemsWriter;
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
        $writer = $this->getOrderItemsWriter();

        $engHeader = $this->_getHeaderColumns();

        $writer->setHeaderCols($engHeader);

        $orderItemData = $this->getItemData();
        if ($orderItemData == null) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('There is no data for the export.'));
            return $resultRedirect->setPath('*/*/index');
        }
        foreach ($orderItemData as $orderData) {
            foreach ($orderData as $itemData) {
                $writer->writeSourceRowWithCustomColumns($itemData, $engHeader);
            }
        }
        return $writer->getContents();
    }

    /**
     * Return Data of orders
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
     * Export given Order data
     * @param Order $item
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
        return "sales_order_item";
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
                ->addFieldToFilter('entity_code', ['eq' => 'sales_order_item'])->getFirstItem();
            $exportDate = $customExportData->getData('updated_at');
            if ($exportDate == "NULL") {
                /** @var CollectionFactory $collection */
                $collection = $this->orderItemsColFactory->create();
            } else {
                /** @var CollectionFactory $collection */
                $collection = $this->orderItemsColFactory->create();
                $collection->addFieldToFilter('updated_at', ['gteq' => $exportDate]);
            }
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $collection;
    }

    /**
     * Get Order Items Writer for CSV File
     * @return OrderItemsCsv
     * @throws LocalizedException
     */
    public function getOrderItemsWriter()
    {
        if (!$this->orderItemsWriter) {
            throw new LocalizedException(__('Please specify the order items writer.'));
        }
        return $this->orderItemsWriter;
    }

    /**
     * Set Order Items Writer for CSV File
     * @param OrderItemsCsv $orderItemsWriter
     * @return $this
     */
    public function setOrderItemsWriter(OrderItemsCsv $orderItemsWriter)
    {
        $this->orderItemsWriter = $orderItemsWriter;
        return $this;
    }
}
