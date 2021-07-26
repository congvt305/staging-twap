<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 29/6/21
 * Time: 12:55 PM
 */
namespace Amore\GcrmDataExport\Model\Export\Order;

use Amore\GcrmDataExport\Model\ResourceModel\CustomImportExport\CollectionFactory as ExportCollection;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Amore\GcrmDataExport\Model\Export\Adapter\OrderCsv;
use Amore\GcrmDataExport\Model\Export\Order\AttributeCollectionProvider;
use Amore\GcrmDataExport\Model\OrderColumnsInterface;
use Magento\ImportExport\Model\Export\AbstractEntity;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Api\OrderRepositoryInterface;
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
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\Collection as DataCollection;

/**
 * Export Sales Order
 */
class SalesOrder extends AbstractEntity implements OrderColumnsInterface
{
    protected $headColumnNames = [
        self::ORDER_ENTITY_ID => 'entity_id',
        self::ORDER_STATE => 'state',
        self::ORDER_STATUS => 'status',
        self::ORDER_COUPON_CODE => 'coupon_code',
        self::ORDER_PROTECT_CODE => 'protect_code',
        self::ORDER_SHIPPING_DESCRIPTION => 'shipping_description',
        self::ORDER_IS_VIRTUAL => 'is_virtual',
        self::ORDER_STORE_ID => 'store_id',
        self::ORDER_CUSTOMER_ID => 'customer_id',
        self::ORDER_BASE_DISCOUNT_AMOUNT => 'base_discount_amount',
        self::ORDER_BASE_DISCOUNT_CANCELED => 'base_discount_canceled',
        self::ORDER_BASE_DISCOUNT_INVOICED => 'base_discount_invoiced',
        self::ORDER_BASE_DISCOUNT_REFUNDED => 'base_discount_refunded',
        self::ORDER_BASE_GRAND_TOTAL => 'base_grand_total',
        self::ORDER_BASE_SHIPPING_AMOUNT => 'base_shipping_amount',
        self::ORDER_BASE_SHIPPING_CANCELED => 'base_shipping_canceled',
        self::ORDER_BASE_SHIPPING_INVOICED => 'base_shipping_invoiced',
        self::ORDER_BASE_SHIPPING_REFUNDED => 'base_shipping_refunded',
        self::ORDER_BASE_SHIPPING_TAX_AMOUNT => 'base_shipping_tax_amount',
        self::ORDER_BASE_SHIPPING_TAX_REFUNDED => 'base_shipping_tax_refunded',
        self::ORDER_BASE_SUBTOTAL => 'base_subtotal',
        self::ORDER_BASE_SUBTOTAL_CANCELED => 'base_subtotal_canceled',
        self::ORDER_BASE_SUBTOTAL_INVOICED => 'base_subtotal_invoiced',
        self::ORDER_BASE_SUBTOTAL_REFUNDED => 'base_subtotal_refunded',
        self::ORDER_BASE_TAX_AMOUNT => 'base_tax_amount',
        self::ORDER_BASE_TAX_CANCELED => 'base_tax_canceled',
        self::ORDER_BASE_TAX_INVOICED => 'base_tax_invoiced',
        self::ORDER_BASE_TAX_REFUNDED => 'base_tax_refunded',
        self::ORDER_BASE_TO_GLOBAL_RATE => 'base_to_global_rate',
        self::ORDER_BASE_TO_ORDER_RATE => 'base_to_order_rate',
        self::ORDER_BASE_TOTAL_CANCELED => 'base_total_canceled',
        self::ORDER_BASE_TOTAL_INVOICED => 'base_total_invoiced',
        self::ORDER_BASE_TOTAL_INVOICED_COST => 'base_total_invoiced_cost',
        self::ORDER_BASE_TOTAL_OFFLINE_REFUNDED => 'base_total_offline_refunded',
        self::ORDER_BASE_TOTAL_ONLINE_REFUNDED => 'base_total_online_refunded',
        self::ORDER_BASE_TOTAL_PAID => 'base_total_paid',
        self::ORDER_BASE_TOTAL_QTY_ORDERED => 'base_total_qty_ordered',
        self::ORDER_BASE_TOTAL_REFUNDED => 'base_total_refunded',
        self::ORDER_DISCOUNT_AMOUNT => 'discount_amount',
        self::ORDER_DISCOUNT_CANCELED => 'discount_canceled',
        self::ORDER_DISCOUNT_INVOICED => 'discount_invoiced',
        self::ORDER_DISCOUNT_REFUNDED => 'discount_refunded',
        self::ORDER_GRAND_TOTAL => 'grand_total',
        self::ORDER_SHIPPING_AMOUNT => 'shipping_amount',
        self::ORDER_SHIPPING_CANCELED => 'shipping_canceled',
        self::ORDER_SHIPPING_INVOICED => 'shipping_invoiced',
        self::ORDER_SHIPPING_REFUNDED => 'shipping_refunded',
        self::ORDER_SHIPPING_TAX_AMOUNT => 'shipping_tax_amount',
        self::ORDER_SHIPPING_TAX_REFUNDED => 'shipping_tax_refunded',
        self::ORDER_STORE_TO_BASE_RATE => 'store_to_base_rate',
        self::ORDER_STORE_TO_ORDER_RATE => 'store_to_order_rate',
        self::ORDER_SUBTOTAL => 'subtotal',
        self::ORDER_SUBTOTAL_CANCELED => 'subtotal_canceled',
        self::ORDER_SUBTOTAL_INVOICED => 'subtotal_invoiced',
        self::ORDER_SUBTOTAL_REFUNDED => 'subtotal_refunded',
        self::ORDER_TAX_AMOUNT => 'tax_amount',
        self::ORDER_TAX_CANCELED => 'tax_canceled',
        self::ORDER_TAX_INVOICED => 'tax_invoiced',
        self::ORDER_TAX_REFUNDED => 'tax_refunded',
        self::ORDER_TOTAL_CANCELED => 'total_canceled',
        self::ORDER_TOTAL_INVOICED => 'total_invoiced',
        self::ORDER_TOTAL_OFFLINE_REFUNDED => 'total_offline_refunded',
        self::ORDER_TOTAL_ONLINE_REFUNDED => 'total_online_refunded',
        self::ORDER_TOTAL_PAID => 'total_paid',
        self::ORDER_TOTAL_QTY_ORDERED => 'total_qty_ordered',
        self::ORDER_TOTAL_REFUNDED => 'total_refunded',
        self::ORDER_CAN_SHIP_PARTIALLY => 'can_ship_partially',
        self::ORDER_CAN_SHIP_PARTIALLY_ITEM => 'can_ship_partially_item',
        self::ORDER_CUSTOMER_IS_GUEST => 'customer_is_guest',
        self::ORDER_CUSTOMER_NOT_NOTIFY => 'customer_note_notify',
        self::ORDER_BILLING_ADDRESS_ID => 'billing_address_id',
        self::ORDER_CUSTOMER_GROUP_ID => 'customer_group_id',
        self::ORDER_EDIT_INCREMENT => 'edit_increment',
        self::ORDER_EMAIL_SENT => 'email_sent',
        self::ORDER_SEND_EMAIL => 'send_email',
        self::ORDER_FORCED_SHIPMENT_WITH_INVOICE => 'forced_shipment_with_invoice',
        self::ORDER_PAYMENT_AUTH_EXPIRATION => 'payment_auth_expiration',
        self::ORDER_QUOTE_ADDRESS_ID => 'quote_address_id',
        self::ORDER_QUOTE_ID => 'quote_id',
        self::ORDER_SHIPPING_ADDRESS_ID => 'shipping_address_id',
        self::ORDER_ADJUSTMENT_NEGATIVE => 'adjustment_negative',
        self::ORDER_ADJUSTMENT_POSITIVE => 'adjustment_positive',
        self::ORDER_BASE_ADJUSTMENT_NEGATIVE => 'base_adjustment_negative',
        self::ORDER_BASE_ADJUSTMENT_POSITIVE => 'base_adjustment_positive',
        self::ORDER_BASE_SHIPPING_DISCOUNT_AMOUNT => 'base_shipping_discount_amount',
        self::ORDER_BASE_SUBTOTAL_INCL_TAX => 'base_subtotal_incl_tax',
        self::ORDER_BASE_TOTAL_DUE => 'base_total_due',
        self::ORDER_PAYMENT_AUTHORIZATION_AMOUNT => 'payment_authorization_amount',
        self::ORDER_SHIPPING_DISCOUNT_AMOUNT => 'shipping_discount_amount',
        self::ORDER_SUBTOTAL_INCL_TAX => 'subtotal_incl_tax',
        self::ORDER_TOTAL_DUE => 'total_due',
        self::ORDER_WEIGHT => 'weight',
        self::ORDER_CUSTOMER_DOB => 'customer_dob',
        self::ORDER_INCREMENT_ID => 'increment_id',
        self::ORDER_APPLIED_RULE_IDS => 'applied_rule_ids',
        self::ORDER_BASE_CURRENCY_CODE => 'base_currency_code',
        self::ORDER_CUSTOMER_EMAIL => 'customer_email',
        self::ORDER_CUSTOMER_FIRSTNAME => 'customer_firstname',
        self::ORDER_CUSTOMER_LASTNAME => 'customer_lastname',
        self::ORDER_CUSTOMER_MIDDLENAME => 'customer_middlename',
        self::ORDER_CUSTOMER_PREFIX => 'customer_prefix',
        self::ORDER_CUSTOMER_SUFFIX => 'customer_suffix',
        self::ORDER_CUSTOMER_TAXVAT => 'customer_taxvat',
        self::ORDER_DISCOUNT_DESCRIPTION => 'discount_description',
        self::ORDER_EXT_CUSTOMER_ID => 'ext_customer_id',
        self::ORDER_EXT_ORDER_ID => 'ext_order_id',
        self::ORDER_GLOBAL_CURRENCY_CODE => 'global_currency_code',
        self::ORDER_HOLD_BEFORE_STATE => 'hold_before_state',
        self::ORDER_HOLD_BEFORE_STATUS => 'hold_before_status',
        self::ORDER_ORDER_CURRENCY_CODE => 'order_currency_code',
        self::ORDER_ORIGINAL_INCREMENT_ID => 'original_increment_id',
        self::ORDER_RELATION_CHILD_ID => 'relation_child_id',
        self::ORDER_RELATION_PARENT_ID => 'relation_parent_id',
        self::ORDER_RELATION_PARENT_REAL_ID => 'relation_parent_real_id',
        self::ORDER_REMOTE_IP => 'remote_ip',
        self::ORDER_SHIPPING_METHOD => 'shipping_method',
        self::ORDER_STORE_CURRENCY_CODE => 'store_currency_code',
        self::ORDER_STORE_NAME => 'store_name',
        self::ORDER_X_FORWARDED_FOR => 'x_forwarded_for',
        self::ORDER_CUSTOMER_NOTE => 'customer_note',
        self::ORDER_CREATED_AT => 'created_at',
        self::ORDER_UPDATED_AT => 'updated_at',
        self::ORDER_TOTAL_ITEM_COUNT => 'total_item_count',
        self::ORDER_CUSTOMER_GENDER => 'customer_gender',
        self::ORDER_DISCOUNT_TAX_COMPENSATION_AMOUNT => 'discount_tax_compensation_amount',
        self::ORDER_BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT => 'base_discount_tax_compensation_amount',
        self::ORDER_SHIPPING_DISCOUNT_TAX_COMPENSATION_AMOUNT => 'shipping_discount_tax_compensation_amount',
        self::ORDER_BASE_SHIPPING_DISCOUNT_TAX_COMPENSATION_AMNT => 'base_shipping_discount_tax_compensation_amnt',
        self::ORDER_DISCOUNT_TAX_COMPENSATION_INVOICED => 'discount_tax_compensation_invoiced',
        self::ORDER_BASE_DISCOUNT_TAX_COMPENSATION_INVOICED => 'base_discount_tax_compensation_invoiced',
        self::ORDER_DISCOUNT_TAX_COMPENSATION_REFUNDED => 'discount_tax_compensation_refunded',
        self::ORDER_BASE_DISCOUNT_TAX_COMPENSATION_REFUNDED => 'base_discount_tax_compensation_refunded',
        self::ORDER_SHIPPING_INCL_TAX => 'shipping_incl_tax',
        self::ORDER_BASE_SHIPPING_INCL_TAX => 'base_shipping_incl_tax',
        self::ORDER_COUPON_RULE_NAME => 'coupon_rule_name',
        self::ORDER_BASE_CUSTOMER_BALANCE_AMOUNT => 'base_customer_balance_amount',
        self::ORDER_CUSTOMER_BALANCE_AMOUNT => 'customer_balance_amount',
        self::ORDER_BASE_CUSTOMER_BALANCE_INVOICED => 'base_customer_balance_invoiced',
        self::ORDER_CUSTOMER_BALANCE_INVOICED => 'customer_balance_invoiced',
        self::ORDER_BASE_CUSTOMER_BALANCE_REFUNDED => 'base_customer_balance_refunded',
        self::ORDER_CUSTOMER_BALANCE_REFUNDED => 'customer_balance_refunded',
        self::ORDER_BS_CUSTOMER_BAL_TOTAL_REFUNDED => 'bs_customer_bal_total_refunded',
        self::ORDER_CUSTOMER_BAL_TOTAL_REFUNDED => 'customer_bal_total_refunded',
        self::ORDER_GIFT_CARDS => 'gift_cards',
        self::ORDER_BASE_GIFT_CARDS_AMOUNT => 'base_gift_cards_amount',
        self::ORDER_GIFT_CARDS_AMOUNT => 'gift_cards_amount',
        self::ORDER_BASE_GIFT_CARDS_INVOICED => 'base_gift_cards_invoiced',
        self::ORDER_GIFT_CARDS_INVOICED => 'gift_cards_invoiced',
        self::ORDER_BASE_GIFT_CARDS_REFUNDED => 'base_gift_cards_refunded',
        self::ORDER_GIFT_CARDS_REFUNDED => 'gift_cards_refunded',
        self::ORDER_GIFT_MESSAGE_ID => 'gift_message_id',
        self::ORDER_GW_ID => 'gw_id',
        self::ORDER_GW_ALLOW_GIFT_RECEIPT => 'gw_allow_gift_receipt',
        self::ORDER_GW_ADD_CARD => 'gw_add_card',
        self::ORDER_GW_BASE_PRICE => 'gw_base_price',
        self::ORDER_GW_PRICE => 'gw_price',
        self::ORDER_GW_ITEMS_BASE_PRICE => 'gw_items_base_price',
        self::ORDER_GW_ITEMS_PRICE => 'gw_items_price',
        self::ORDER_GW_CARD_BASE_PRICE => 'gw_card_base_price',
        self::ORDER_GW_CARD_PRICE => 'gw_card_price',
        self::ORDER_GW_BASE_TAX_AMOUNT => 'gw_base_tax_amount',
        self::ORDER_GW_TAX_AMOUNT => 'gw_tax_amount',
        self::ORDER_GW_ITEMS_BASE_TAX_AMOUNT => 'gw_items_base_tax_amount',
        self::ORDER_GW_ITEMS_TAX_AMOUNT => 'gw_items_tax_amount',
        self::ORDER_GW_CARD_BASE_TAX_AMOUNT => 'gw_card_base_tax_amount',
        self::ORDER_GW_CARD_TAX_AMOUNT => 'gw_card_tax_amount',
        self::ORDER_GW_BASE_PRICE_INCL_TAX => 'gw_base_price_incl_tax',
        self::ORDER_GW_PRICE_INCL_TAX => 'gw_price_incl_tax',
        self::ORDER_GW_ITEMS_BASE_PRICE_INCL_TAX => 'gw_items_base_price_incl_tax',
        self::ORDER_GW_ITEMS_PRICE_INCL_TAX => 'gw_items_price_incl_tax',
        self::ORDER_GW_CARD_BASE_PRICE_INCL_TAX => 'gw_card_base_price_incl_tax',
        self::ORDER_GW_CARD_PRICE_INCL_TAX => 'gw_card_price_incl_tax',
        self::ORDER_GW_BASE_PRICE_INVOICED => 'gw_base_price_invoiced',
        self::ORDER_GW_PRICE_INVOICED => 'gw_price_invoiced',
        self::ORDER_GW_ITEMS_BASE_PRICE_INVOICED => 'gw_items_base_price_invoiced',
        self::ORDER_GW_ITEMS_PRICE_INVOICED => 'gw_items_price_invoiced',
        self::ORDER_GW_CARD_BASE_PRICE_INVOICED => 'gw_card_base_price_invoiced',
        self::ORDER_GW_CARD_PRICE_INVOICED => 'gw_card_price_invoiced',
        self::ORDER_GW_BASE_TAX_AMOUNT_INVOICED => 'gw_base_tax_amount_invoiced',
        self::ORDER_GW_TAX_AMOUNT_INVOICED => 'gw_tax_amount_invoiced',
        self::ORDER_GW_ITEMS_BASE_TAX_INVOICED => 'gw_items_base_tax_invoiced',
        self::ORDER_GW_ITEMS_TAX_INVOICED => 'gw_items_tax_invoiced',
        self::ORDER_GW_CARD_BASE_TAX_INVOICED => 'gw_card_base_tax_invoiced',
        self::ORDER_GW_CARD_TAX_INVOICED => 'gw_card_tax_invoiced',
        self::ORDER_GW_BASE_PRICE_REFUNDED => 'gw_base_price_refunded',
        self::ORDER_GW_PRICE_REFUNDED => 'gw_price_refunded',
        self::ORDER_GW_ITEMS_BASE_PRICE_REFUNDED => 'gw_items_base_price_refunded',
        self::ORDER_GW_ITEMS_PRICE_REFUNDED => 'gw_items_price_refunded',
        self::ORDER_GW_CARD_BASE_PRICE_REFUNDED => 'gw_card_base_price_refunded',
        self::ORDER_GW_CARD_PRICE_REFUNDED => 'gw_card_price_refunded',
        self::ORDER_GW_BASE_TAX_AMOUNT_REFUNDED => 'gw_base_tax_amount_refunded',
        self::ORDER_GW_TAX_AMOUNT_REFUNDED => 'gw_tax_amount_refunded',
        self::ORDER_GW_ITEMS_BASE_TAX_REFUNDED => 'gw_items_base_tax_refunded',
        self::ORDER_GW_ITEMS_TAX_REFUNDED => 'gw_items_tax_refunded',
        self::ORDER_GW_CARD_BASE_TAX_REFUNDED => 'gw_card_base_tax_refunded',
        self::ORDER_GW_CARD_TAX_REFUNDED => 'gw_card_tax_refunded',
        self::ORDER_PAYPAL_IPN_CUSTOMER_NOTIFIED => 'paypal_ipn_customer_notified',
        self::ORDER_REWARD_POINTS_BALANCE => 'reward_points_balance',
        self::ORDER_BASE_REWARD_CURRENCY_AMOUNT => 'base_reward_currency_amount',
        self::ORDER_REWARD_CURRENCY_AMOUNT => 'reward_currency_amount',
        self::ORDER_BASE_RWRD_CRRNCY_AMT_INVOICED => 'base_rwrd_crrncy_amt_invoiced',
        self::ORDER_RWRD_CRRNCY_AMOUNT_INVOICED => 'rwrd_currency_amount_invoiced',
        self::ORDER_BASE_RWRD_CRRNCY_AMNT_REFNDED => 'base_rwrd_crrncy_amnt_refnded',
        self::ORDER_RWRD_CRRNCY_AMNT_REFUNDED => 'rwrd_crrncy_amnt_refunded',
        self::ORDER_REWARD_POINTS_BALANCE_REFUND => 'reward_points_balance_refund',
        self::ORDER_DELIVERY_MESSAGE => 'delivery_message',
        self::ORDER_SAP_ORDER_SEND_CHECK => 'sap_order_send_check',
        self::ORDER_ECPAY_PAYMENT_METHOD => 'ecpay_payment_method',
        self::ORDER_SAP_RESPONSE => 'sap_response',
        self::ORDER_RMA_VALID_DATE => 'rma_valid_date',
        self::ORDER_PAYPAL_ORDER_SUBTOTAL => 'paypal_subtotal',
        self::ORDER_PAYPAL_GRAND_TOTAL => 'paypal_grand_total',
        self::ORDER_PAYPAL_TAX_AMOUNT => 'paypal_tax_amount',
        self::ORDER_PAYPAL_SHIPPING_AMOUNT => 'paypal_shipping_amount',
        self::ORDER_PAYPAL_DISCOUNT_AMOUNT => 'paypal_discount_amount',
        self::ORDER_PAYPAL_RATE => 'paypal_rate',
        self::ORDER_PAYPAL_CURRENCY_CODE => 'paypal_currency_code',
        self::ORDER_CUSTOMER_BA_CODE => 'customer_ba_code',
        self::ORDER_POS_ORDER_SEND_CHECK => 'pos_order_send_check',
        self::ORDER_ADYEN_RESULTURL_EVENT_CODE => 'adyen_resulturl_event_code',
        self::ORDER_ADYEN_NOTIFICATION_EVENT_CODE => 'adyen_notification_event_code',
        self::ORDER_ADYEN_NOTIFICATION_EVENT_CODE_SUCCESS => 'adyen_notification_event_code_success',
        self::ORDER_ADYEN_CHARGED_CURRENCY => 'adyen_charged_currency',
        self::ORDER_SAP_NSAMT => 'sap_nsamt',
        self::ORDER_SAP_DCAMT => 'sap_dcamt',
        self::ORDER_SAP_SLAMT => 'sap_slamt',
    ];

    /**
     * @var OrderCsv
     */
    private $orderWriter;

    /**
     * @var CollectionFactory
     */
    private $orderColFactory;

    /**
     * @var AttributeCollectionProvider
     */
    private $attributeCollectionProvider;

    /**
     * @var Collection
     */
    private $orderCollection;

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

    private $timezone;

    /** Attribute collection name.
     * Used to resolve entity attribute collection.
     */
    const ATTRIBUTE_COLLECTION_NAME = Collection::class;

    /**
     * SalesOrder constructor.
     * @param ExportCollection $ExportCollectionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param AttributeCollectionProvider $attributeCollectionProvider
     * @param CollectionFactory $orderColFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param OrderCsv $orderWriter
     * @param Factory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param array $data
     */
    public function __construct(
        ExportCollection $ExportCollectionFactory,
        OrderRepositoryInterface $orderRepository,
        AttributeCollectionProvider $attributeCollectionProvider,
        CollectionFactory $orderColFactory,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        OrderCsv  $orderWriter,
        Factory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        TimezoneInterface $timezone,
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
        $this->orderColFactory = $orderColFactory;
        $this->attributeCollectionProvider = $attributeCollectionProvider;
        $this->orderRepository = $orderRepository;
        $this->orderWriter = $orderWriter;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->timezone = $timezone;
    }

    /**
     * Main Export Function
     * @return string
     * @throws LocalizedException
     * @throws FileSystemException
     */
    public function export()
    {
        $writer = $this->getOrderWriter();

        $engHeader = $this->_getHeaderColumns();

        $writer->setHeaderCols($engHeader);

        $ordersData = $this->getOrdersData();
        if ($ordersData == null) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('There is no data for the export.'));
            return false;
        }

        foreach ($ordersData as $orders) {
            foreach ($orders as $singleOrder) {
                $writer->writeSourceRowWithCustomColumns($singleOrder, $engHeader);
            }
        }
        return $writer->getContents();
    }

    /**
     * Return Data of orders
     * @return array
     */
    public function getOrdersData()
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
     * @param Order $order
     * @return void
     */
    public function exportItem($order)
    {
        // TODO: Implement exportItem() method.
    }

    /**
     * @return string
     *
     */
    public function getEntityTypeCode()
    {
        return "order";
    }

    /**
     * This function gets header columns
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
     * @return AbstractDb|void
     */
    protected function _getEntityCollection()
    {
        // will not implement this method as it is legacy interface
    }

    /**
     * This function gets attributes cllectionn
     * @return DataCollection
     * @throws \Exception
     */
    public function getAttributeCollection()
    {
        return $this->attributeCollectionProvider->get();
    }

    /**
     * This function gets orders cllectionn
     * @return CollectionFactory
     */
    public function joinedItemCollection()
    {
        try {
            $customExportData = $this->ExportCollectionFactory->create()
                ->addFieldToFilter('entity_code', ['eq' => 'order'])->getFirstItem();
            $exportDate = $customExportData->getData('updated_at');
            if ($exportDate == "NULL") {
                /** @var CollectionFactory $collection */
                $collection = $this->orderColFactory->create();
            } else {
                /** @var CollectionFactory $collection */
                $collection = $this->orderColFactory->create();
                $collection->addFieldToFilter('updated_at', ['gteq' => $exportDate]);
            }
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $collection;
    }

    /**
     * **
     * Get Order Writer for CSV File
     * @return OrderCsv
     * @throws LocalizedException
     */
    public function getOrderWriter()
    {
        if (!$this->orderWriter) {
            throw new LocalizedException(__('Please specify the order writer.'));
        }
        return $this->orderWriter;
    }

    /**
     * Set Order Writer for CSV File
     * @param OrderCsv $orderWriter
     * @return $this
     */
    public function setOrderWriter(OrderCsv $orderWriter)
    {
        $this->orderWriter = $orderWriter;
        return $this;
    }
}
