<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 5/7/21
 * Time: 10:00 AM
 */
namespace Amore\GcrmDataExport\Model;

/**
 * Interface For Declaring Constants
 * Interface QuoteItemsColumnsInterface
 */
interface QuoteItemsColumnsInterface
{
    const QUOTE_ITEM_ITEM_ID = 'item_id';
    const QUOTE_ITEM_QUOTE_ID = 'quote_id';
    const QUOTE_ITEM_CREATED_AT = 'created_at';
    const QUOTE_ITEM_UPDATED_AT = 'updated_at';
    const QUOTE_ITEM_PRODUCT_ID = 'product_id';
    const QUOTE_ITEM_STORE_ID = 'store_id';
    const QUOTE_ITEM_PARENT_ITEM_ID = 'parent_item_id';
    const QUOTE_ITEM_IS_VIRTUAL = 'is_virtual';
    const QUOTE_ITEM_SKU = 'sku';
    const QUOTE_ITEM_NAME = 'name';
    const QUOTE_ITEM_DESCRIPTION = 'description';
    const QUOTE_ITEM_APPLIED_RULE_IDS = 'applied_rule_ids';
    const QUOTE_ITEM_ADDITIONAL_DATA = 'additional_data';
    const QUOTE_ITEM_IS_QTY_DECIMAL = 'is_qty_decimal';
    const QUOTE_ITEM_NO_DISCOUNT = 'no_discount';
    const QUOTE_ITEM_WEIGHT = 'weight';
    const QUOTE_ITEM_QTY = 'qty';
    const QUOTE_ITEM_PRICE = 'price';
    const QUOTE_ITEM_BASE_PRICE = 'base_price';
    const QUOTE_ITEM_CUSTOM_PRICE = 'custom_price';
    const QUOTE_ITEM_DISCOUNT_PERCENT = 'discount_percent';
    const QUOTE_ITEM_DISCOUNT_AMOUNT = 'discount_amount';
    const QUOTE_ITEM_BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
    const QUOTE_ITEM_TAX_PERCENT = 'tax_percent';
    const QUOTE_ITEM_TAX_AMOUNT = 'tax_amount';
    const QUOTE_ITEM_BASE_TAX_AMOUNT = 'base_tax_amount';
    const QUOTE_ITEM_ROW_TOTAL = 'row_total';
    const QUOTE_ITEM_BASE_ROW_TOTAL = 'base_row_total';
    const QUOTE_ITEM_ROW_TOTAL_WITH_DISCOUNT = 'row_total_with_discount';
    const QUOTE_ITEM_ROW_WEIGHT = 'row_weight';
    const QUOTE_ITEM_PRODUCT_TYPE = 'product_type';
    const QUOTE_ITEM_BASE_TAX_BEFORE_DISCOUNT = 'base_tax_before_discount';
    const QUOTE_ITEM_TAX_BEFORE_DISCOUNT = 'tax_before_discount';
    const QUOTE_ITEM_ORIGINAL_CUSTOM_PRICE = 'original_custom_price';
    const QUOTE_ITEM_REDIRECT_URL = 'redirect_url';
    const QUOTE_ITEM_BASE_COST = 'base_cost';
    const QUOTE_ITEM_PRICE_INCL_TAX = 'price_incl_tax';
    const QUOTE_ITEM_BASE_PRICE_INCL_TAX = 'base_price_incl_tax';
    const QUOTE_ITEM_ROW_TOTAL_INCL_TAX = 'row_total_incl_tax';
    const QUOTE_ITEM_BASE_ROW_TOTAL_INCL_TAX = 'base_row_total_incl_tax';
    const QUOTE_ITEM_DISCOUNT_TAX_COMPENSATION_AMOUNT = 'discount_tax_compensation_amount';
    const QUOTE_ITEM_BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT = 'base_discount_tax_compensation_amount';
    const QUOTE_ITEM_EVENT_ID = 'event_id';
    const QUOTE_ITEM_GIFT_MESSAGE_ID = 'gift_message_id';
    const QUOTE_ITEM_GW_ID = 'gw_id';
    const QUOTE_ITEM_GW_BASE_PRICE = 'gw_base_price';
    const QUOTE_ITEM_GW_PRICE = 'gw_price';
    const QUOTE_ITEM_GW_BASE_TAX_AMOUNT = 'gw_base_tax_amount';
    const QUOTE_ITEM_GW_TAX_AMOUNT = 'gw_tax_amount';
    const QUOTE_ITEM_WEEE_TAX_APPLIED = 'weee_tax_applied';
    const QUOTE_ITEM_WEEE_TAX_APPLIED_AMOUNT = 'weee_tax_applied_amount';
    const QUOTE_ITEM_WEEE_TAX_APPLIED_ROW_AMOUNT = 'weee_tax_applied_row_amount';
    const QUOTE_ITEM_WEEE_TAX_DISPOSITION = 'weee_tax_disposition';
    const QUOTE_ITEM_WEEE_TAX_ROW_DISPOSITION = 'weee_tax_row_disposition';
    const QUOTE_ITEM_BASE_WEEE_TAX_APPLIED_AMOUNT = 'base_weee_tax_applied_amount';
    const QUOTE_ITEM_BASE_WEEE_TAX_APPLIED_ROW_AMOUNT = 'base_weee_tax_applied_row_amnt';
    const QUOTE_ITEM_BASE_WEEE_TAX_DISPOSITION = 'base_weee_tax_disposition';
    const QUOTE_ITEM_BASE_WEEE_TAX_ROW_DISPOSITION = 'base_weee_tax_row_disposition';
    const QUOTE_ITEM_FREE_SHIPPING = 'free_shipping';
    const QUOTE_ITEM_GIFTREGISTRY_ITEM_ID = 'giftregistery_item_id';
    const QUOTE_ITEM_PAYPAL_PRICE = 'paypal_price';
    const QUOTE_ITEM_PAYPAL_ROW_TOATL = 'paypal_row_total';
}
