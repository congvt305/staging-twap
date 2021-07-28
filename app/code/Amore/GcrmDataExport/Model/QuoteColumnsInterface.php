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
 * Interface QuoteColumnsInterface
 */
interface QuoteColumnsInterface
{
    const QUOTE_ENTITY_ID = 'entity_id';
    const QUOTE_STORE_ID = 'store_id';
    const QUOTE_CREATED_AT = 'created_at';
    const QUOTE_UPDATED_AT = 'updated_at';
    const QUOTE_CONVERTED_AT = 'converted_at';
    const QUOTE_IS_ACTIVE = 'is_active';
    const QUOTE_IS_VIRTUAL = 'is_virtual';
    const QUOTE_IS_MULTI_SHIPPING = 'is_multi_shipping';
    const QUOTE_ITEMS_COUNT = 'items_count';
    const QUOTE_ITEMS_QTY = 'items_qty';
    const QUOTE_ORIG_ORDER_ID = 'orig_order_id';
    const QUOTE_STORE_TO_BASE_RATE = 'store_to_base_rate';
    const QUOTE_STORE_TO_QUOTE_RATE = 'store_to_quote_rate';
    const QUOTE_BASE_CURRENCY_CODE = 'base_currency_code';
    const QUOTE_STORE_CURRENCY_CODE = 'store_currency_code';
    const QUOTE_GRAND_TOTAL = 'grand_total';
    const QUOTE_BASE_GRAND_TOTAL = 'base_grand_total';
    const QUOTE_CHECKOUT_METHOD = 'checkout_method';
    const QUOTE_CUSTOMER_ID = 'customer_id';
    const QUOTE_CUSTOMER_TAX_CLASS_ID = 'customer_tax_class_id';
    const QUOTE_CUSTOMER_GROUP_ID = 'customer_group_id';
    const QUOTE_CUSTOMER_EMAIL = 'customer_email';
    const QUOTE_CUSTOMER_PREFIX = 'customer_prefix';
    const QUOTE_CUSTOMER_FIRSTNAME = 'customer_firstname';
    const QUOTE_CUSTOMER_MIDDLENAME = 'customer_middlename';
    const QUOTE_CUSTOMER_LASTNAME = 'customer_lastname';
    const QUOTE_CUSTOMER_SUFFIX = 'customer_suffix';
    const QUOTE_CUSTOMER_DOB = 'customer_dob';
    const QUOTE_CUSTOMER_NOTE = 'customer_note';
    const QUOTE_CUSTOMER_NOTE_NOTIFY = 'customer_note_notify';
    const QUOTE_CUSTOMER_IS_GUEST = 'customer_is_guest';
    const QUOTE_REMOTE_IP = 'remote_ip';
    const QUOTE_APPLIED_RULE_IDS = 'applied_rule_ids';
    const QUOTE_RESERVED_ORDER_ID = 'reserved_order_id';
    const QUOTE_PASSWORD_HASH = 'password_hash';
    const QUOTE_COUPON_CODE = 'coupon_code';
    const QUOTE_GLOBAL_CURRENCY_CODE = 'global_currency_code';
    const QUOTE_BASE_TO_GLOBAL_RATE = 'base_to_global_rate';
    const QUOTE_BASE_TO_QUOTE_RATE = 'base_to_quote_rate';
    const QUOTE_CUSTOMER_TAXVAT = 'customer_taxvat';
    const QUOTE_CUSTOMER_GENDER = 'customer_gender';
    const QUOTE_SUBTOTAL = 'subtotal';
    const QUOTE_BASE_SUBTOTAL = 'base_subtotal';
    const QUOTE_SUBTOTAL_WITH_DSCOUNT = 'subtotal_with_discount';
    const QUOTE_BASE_SUBTOTAL_WITH_DSCOUNT = 'base_subtotal_with_discount';
    const QUOTE_IS_CHANGED = 'is_changed';
    const QUOTE_TRIGGER_RECOLLECT = 'trigger_recollect';
    const QUOTE_EXT_SHIPPING_INFO = 'ext_shipping_info';
    const QUOTE_CUSTOMER_BALANCE_AMOUNT = 'customer_balance_amount_used';
    const QUOTE_BASE_CUSTOMER_BAL_AMOUNT = 'base_customer_bal_amount_used';
    const QUOTE_USE_CUSTOMER_BALANCE = 'use_customer_balance';
    const QUOTE_GIFT_CARDS = 'gift_cards';
    const QUOTE_GIFT_CARDS_AMOUNT = 'gift_cards_amount';
    const QUOTE_BASE_GIFT_CARDS_AMOUNT = 'base_gift_cards_amount';
    const QUOTE_GIFT_CARDS_AMOUNT_USED = 'gift_cards_amount_used';
    const QUOTE_BASE_GIFT_CARDS_AMOUNT_USED = 'base_gift_cards_amount_used';
    const QUOTE_GIFT_MESSAGE_ID = 'gift_message_id';
    const QUOTE_GW_ID = 'gw_id';
    const QUOTE_GW_ALLOW_GIFT_RECEIPT = 'gw_allow_gift_receipt';
    const QUOTE_GW_ADD_CARD = 'gw_add_card';
    const QUOTE_GW_BASE_PRICE = 'gw_base_price';
    const QUOTE_GW_PRICE = 'gw_price';
    const QUOTE_GW_ITEMS_BASE_PRICE = 'gw_items_base_price';
    const QUOTE_GW_ITEMS_PRICE = 'gw_items_price';
    const QUOTE_GW_CARD_BASE_PRICE = 'gw_card_base_price';
    const QUOTE_GW_CARD_PRICE = 'gw_card_price';
    const QUOTE_GW_BASE_TAX_AMOUNT = 'gw_base_tax_amount';
    const QUOTE_GW_TAX_AMOUNT = 'gw_tax_amount';
    const QUOTE_GW_ITEMS_BASE_TAX_AMOUNT = 'gw_items_base_tax_amount';
    const QUOTE_GW_ITEMS_TAX_AMOUNT = 'gw_items_tax_amount';
    const QUOTE_GW_CARD_BASE_TAX_AMOUNT = 'gw_card_base_tax_amount';
    const QUOTE_GW_CARD_TAX_AMOUNT = 'gw_card_tax_amount';
    const QUOTE_GW_BASE_PRICE_INCL_TAX = 'gw_base_price_incl_tax';
    const QUOTE_GW_PRICE_INCL_TAX = 'gw_price_incl_tax';
    const QUOTE_GW_ITEMS_BASE_PRICE_INCL_TAX = 'gw_items_base_price_incl_tax';
    const QUOTE_GW_ITEMS_PRICE_INCL_TAX = 'gw_items_price_incl_tax';
    const QUOTE_GW_CARD_BASE_PRICE_INCL_TAX = 'gw_card_base_price_incl_tax';
    const QUOTE_GW_CARD_PRICE_INCL_TAX = 'gw_card_price_incl_tax';
    const QUOTE_IS_PERSISTENT = 'is_persistent';
    const QUOTE_USE_REWARD_POINTS = 'use_reward_points';
    const QUOTE_REWARD_POINTS_BALANCE = 'reward_points_balance';
    const QUOTE_BASE_REWARD_CURRENCY_AMOUNT = 'base_reward_currency_amount';
    const QUOTE_REWARD_CURRENCY_AMOUNT = 'reward_currency_amount';
    const QUOTE_DELIVERY_MESSAGE = 'delivery_message';
    const QUOTE_PAYPAL_ORDER_SUBTOTAL = 'paypal_subtotal';
    const QUOTE_PAYPAL_GRAND_TOTAL = 'paypal_grand_total';
    const QUOTE_PAYPAL_TAX_AMOUNT = 'paypal_tax_amount';
    const QUOTE_PAYPAL_SHIPPING_AMOUNT = 'paypal_shipping_amount';
    const QUOTE_PAYPAL_DISCOUNT_AMOUNT = 'paypal_discount_amount';
    const QUOTE_PAYPAL_RATE = 'paypal_rate';
    const QUOTE_PAYPAL_CURRENCY_CODE = 'paypal_currency_code';
}
