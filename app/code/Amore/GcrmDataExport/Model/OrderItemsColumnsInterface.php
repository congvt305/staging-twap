<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 2/7/21
 * Time: 2:13 PM
 */
namespace Amore\GcrmDataExport\Model;

/**
 * Interface For Declaring Constants
 * Interface OrderItemsColumnsInterface
 */
interface OrderItemsColumnsInterface
{
    const ORDER_ITEM_ITEM_ID = 'item_id';
    const ORDER_ITEM_ORDER_ID = 'ORDER_ITEM_id';
    const ORDER_ITEM_PARENT_ITEM_ID = 'parent_item_id';
    const ORDER_ITEM_QUOTE_ITEM_ID = 'quote_item_id';
    const ORDER_ITEM_STORE_ID = 'store_id';
    const ORDER_ITEM_CREATED_AT = 'created_at';
    const ORDER_ITEM_UPDATED_AT = 'updated_at';
    const ORDER_ITEM_PRODUCT_ID = 'product_id';
    const ORDER_ITEM_PRODUCT_TYPE = 'product_type';
    const ORDER_ITEM_PRODUCT_OPTIONS = 'product_options';
    const ORDER_ITEM_WEIGHT = 'weight';
    const ORDER_ITEM_IS_VIRTUAL = 'is_virtual';
    const ORDER_ITEM_PRODUCT_SKU = 'sku';
    const ORDER_ITEM_PRODUCT_NAME = 'name';
    const ORDER_ITEM_DESCRIPTION = 'description';
    const ORDER_ITEM_APPLIED_RULE_IDS = 'applied_rule_ids';
    const ORDER_ITEM_ADDITIONAL_DATA = 'additional_data';
    const ORDER_ITEM_IS_QTY_DECIMAL = 'is_qty_decimal';
    const ORDER_ITEM_NO_DISCOUNT = 'no_discount';
    const ORDER_ITEM_QTY_BACKORDERED = 'qty_backordered';
    const ORDER_ITEM_QTY_CANCELED = 'qty_canceled';
    const ORDER_ITEM_QTY_INVOICED = 'qty_invoiced';
    const ORDER_ITEM_QTY_ORDERED = 'qty_ordered';
    const ORDER_ITEM_QTY_REFUNDED = 'qty_refunded';
    const ORDER_ITEM_QTY_SHIPPED = 'qty_shipped';
    const ORDER_ITEM_BASE_COST = 'base_cost';
    const ORDER_ITEM_PRICE = 'price';
    const ORDER_ITEM_BASE_PRICE = 'base_price';
    const ORDER_ITEM_ORIGINAL_PRICE = 'original_price';
    const ORDER_ITEM_BASE_ORIGINAL_PRICE = 'base_original_price';
    const ORDER_ITEM_TAX_PERCENT = 'tax_percent';
    const ORDER_ITEM_TAX_AMOUNT = 'tax_amount';
    const ORDER_ITEM_BASE_TAX_AMOUNT = 'base_tax_amount';
    const ORDER_ITEM_TAX_INVOICED = 'tax_invoiced';
    const ORDER_ITEM_BASE_TAX_INVOICED = 'base_tax_invoiced';
    const ORDER_ITEM_DISCOUNT_PERCENT = 'discount_percent';
    const ORDER_ITEM_DISCOUNT_AMOUNT = 'discount_amount';
    const ORDER_ITEM_INCREMENT_ID = 'increment_id';
    const ORDER_ITEM_BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
    const ORDER_ITEM_DISCOUNT_INVOICED = 'discount_invoiced';
    const ORDER_ITEM_BASE_DISCOUNT_INVOICED = 'base_discount_invoiced';
    const ORDER_ITEM_AMOUNT_REFUNDED = 'amount_refunded';
    const ORDER_ITEM_BASE_AMOUNT_REFUNDED = 'base_amount_refunded';
    const ORDER_ITEM_ROW_TOTAL = 'row_total';
    const ORDER_ITEM_BASE_ROW_TOTAL = 'base_row_total';
    const ORDER_ITEM_ROW_INVOICED = 'row_invoiced';
    const ORDER_ITEM_BASE_ROW_INVOICED = 'base_row_invoiced';
    const ORDER_ITEM_ROW_WEIGHT = 'row_weight';
    const ORDER_ITEM_BASE_TAX_BEFORE_DISCOUNT = 'base_tax_before_discount';
    const ORDER_ITEM_TAX_BEFORE_DISCOUNT = 'tax_before_discount';
    const ORDER_ITEM_EXT_ORDER_ITEM_ID = 'ext_order_item_id';
    const ORDER_ITEM_LOCKED_DO_INVOICE = 'locked_do_invoice';
    const ORDER_ITEM_LOCKED_DO_SHIP = 'locked_do_ship';
    const ORDER_ITEM_PRICE_INCL_TAX = 'price_incl_tax';
    const ORDER_ITEM_BASE_PRICE_INCL_TAX = 'base_price_incl_tax';
    const ORDER_ITEM_ROW_TOTAL_INCL_TAX = 'row_total_incl_tax';
    const ORDER_ITEM_BASE_ROW_TOTAL_INCL_TAX = 'base_row_total_incl_tax';
    const ORDER_ITEM_DISCOUNT_TAX_COMPENSATION_AMOUNT = 'discount_tax_compensation_amount';
    const ORDER_ITEM_BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT = 'base_discount_tax_compensation_amount';
    const ORDER_ITEM_DISCOUNT_TAX_COMPENSATION_INVOICED = 'discount_tax_compensation_invoiced';
    const ORDER_ITEM_BASE_DISCOUNT_TAX_COMPENSATION_INVOICED = 'base_discount_tax_compensation_invoiced';
    const ORDER_ITEM_DISCOUNT_TAX_COMPENSATION_REFUNDED = 'discount_tax_compensation_refunded';
    const ORDER_ITEM_BASE_DISCOUNT_TAX_COMPENSATION_REFUNDED = 'base_discount_tax_compensation_refunded';
    const ORDER_ITEM_TAX_CANCELED = 'tax_canceled';
    const ORDER_ITEM_DISCOUNT_TAX_COMPENSATION_CANCELED = 'discount_tax_compensation_canceled';
    const ORDER_ITEM_TAX_REFUNDED = 'tax_refunded';
    const ORDER_ITEM_BASE_TAX_REFUNDED = 'base_tax_refunded';
    const ORDER_ITEM_DISCOUNT_REFUNDED = 'discount_refunded';
    const ORDER_ITEM_BASE_DISCOUNT_REFUNDED = 'base_discount_refunded';
    const ORDER_ITEM_EVENT_ID = 'event_id';
    const ORDER_ITEM_GIFT_MESSAGE_ID = 'gift_message_id';
    const ORDER_ITEM_GIFT_MESSAGE_AVAILABLE = 'gift_message_available';
    const ORDER_ITEM_GW_ID = 'gw_id';
    const ORDER_ITEM_GW_BASE_PRICE = 'gw_base_price';
    const ORDER_ITEM_GW_PRICE = 'gw_price';
    const ORDER_ITEM_GW_BASE_TAX_AMOUNT = 'gw_base_tax_amount';
    const ORDER_ITEM_GW_TAX_AMOUNT = 'gw_tax_amount';
    const ORDER_ITEM_GW_BASE_PRICE_INVOICED = 'gw_base_price_invoiced';
    const ORDER_ITEM_GW_PRICE_INVOICED = 'gw_price_invoiced';
    const ORDER_ITEM_GW_BASE_TAX_AMOUNT_INVOICED = 'gw_base_tax_amount_invoiced';
    const ORDER_ITEM_GW_TAX_AMOUNT_INVOICED = 'gw_tax_amount_invoiced';
    const ORDER_ITEM_GW_BASE_PRICE_REFUNDED = 'gw_base_price_refunded';
    const ORDER_ITEM_GW_PRICE_REFUNDED = 'gw_price_refunded';
    const ORDER_ITEM_GW_BASE_TAX_AMOUNT_REFUNDED = 'gw_base_tax_amount_refunded';
    const ORDER_ITEM_GW_TAX_AMOUNT_REFUNDED = 'gw_tax_amount_refunded';
    const ORDER_ITEM_WEEE_TAX_APPLIED = 'weee_tax_applied';
    const ORDER_ITEM_WEEE_TAX_APPLIED_AMOUNT = 'weee_tax_applied_amount';
    const ORDER_ITEM_WEEE_TAX_APPLIED_ROW_AMOUNT = 'weee_tax_applied_row_amount';
    const ORDER_ITEM_WEEE_TAX_DISPOSITION = 'weee_tax_disposition';
    const ORDER_ITEM_WEEE_TAX_ROW_DISPOSITION = 'weee_tax_row_disposition';
    const ORDER_ITEM_BASE_WEEE_TAX_APPLIED_AMOUNT = 'base_weee_tax_applied_amount';
    const ORDER_ITEM_BASE_WEEE_TAX_APPLIED_ROW_AMOUNT = 'base_weee_tax_applied_row_amnt';
    const ORDER_ITEM_BASE_WEEE_TAX_DISPOSITION = 'base_weee_tax_disposition';
    const ORDER_ITEM_BASE_WEEE_TAX_ROW_DISPOSITION = 'base_weee_tax_row_disposition';
    const ORDER_ITEM_FREE_SHIPPING = 'free_shipping';
    const ORDER_ITEM_GIFTREGISTRY_ITEM_ID = 'giftregistry_item_id';
    const ORDER_ITEM_QTY_RETURNED = 'qty_returned';
    const ORDER_ITEM_PAYPAL_PRICE = 'paypal_price';
    const ORDER_ITEM_PAYPAL_ROW_TOATL = 'paypal_row_total';
    const ORDER_ITEM_SAP_ITEM_NSAMT = 'sap_item_nsamt';
    const ORDER_ITEM_SAP_ITEM_DCAMT = 'sap_item_dcamt';
    const ORDER_ITEM_SAP_ITEM_SLAMT = 'sap_item_slamt';
    const ORDER_ITEM_SAP_ITEM_NETWR = 'sap_item_netwr';
}
