<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-12
 * Time: 오후 2:45
 */

namespace Amore\Sap\Api\Data;

/**
 * Interface SapOrderConfirmInterface
 * @api
 */
interface SapOrderConfirmInterface
{
    const SALES_ORGANIZATION = 'sales_organization';

    const MALL_CODE = 'mall_code';

    const ORIGINAL_SALES_ORGANIZATION = 'original_sales_organization';

    const ORIGINAL_MALL_CODE = 'original_mall_code';

    const ORIGINAL_ORDER_INCREMENT_ID = 'original_order_increment_id';

    const ORDER_INCREMENT_ID = 'order_increment_id';

    const ORDER_CREATED_AT = 'order_created_at';

    const PAYMENT_METHOD = 'payment_method';

    const INVOICE_CREATED_AT = 'invoice_created_at';

    const ORDER_TYPE = 'order_type';

    const ORDER_REASON_CODE = 'order_reason_code';

    const ORDER_REASON_TEXT = 'order_reason_text';

    const USAGE_INDICATOR = 'usage_indicator';

    const CUSTOMER_CODE = 'customer_code';

    const CUSTOMER_NAME = 'customer_name';

    const SHIPPING_ADDRESS_CODE = 'shipping_address_code';

    const SHIPPING_NAME = 'shipping_name';

    const SHIPPING_POSTCODE = 'shipping_postcode';

    const SHIPPING_REGION = 'shipping_region';

    const SHIPPING_CITY = 'shipping_city';

    const SHIPPING_STREET = 'shipping_street';

    const SHIPPING_COUNTRY_ID = 'shipping_country_id';

    const SHIPPING_TELEPHONE = 'shipping_telephone';

    const ORDER_CURRENCY_CODE = 'order_currency_code';

    const SUBTOTAL_INCL_TAX = 'subtotal_incl_tax';

    const DISCOUNT_AMOUNT = 'discount_amount';

    const GRAND_TOTAL = 'grand_total';

    const REWARD_POINTS_USED = 'rewatd_points_used';

    const SHIPPING_AMOUNT = 'shipping_amount';

    const TAX_AMOUNT = 'tax_amount';

    const SHIPPING_AMOUNT_PAYING_SUBJECT = 'shipping_amount_paying_subject';

    const ORDER_ITEM_COUNT = 'order_item_count';

    const SALES_PLANT = 'sales_plant';

    const SALES_STORE_LOCATION = 'sales_store_location';

    const RMA_NO = 'rma_no';

    const ORDER_ITEM_DATA = 'order_item_data';

    /**
     * @return string
     */
    public function getSalesOrganization();

    /**
     * @param string $salesOrganization
     */
    public function setSalesOrganization($salesOrganization);

    /**
     * @return string
     */
    public function getMallCode();

    /**
     * @param string $mallCode
     */
    public function setMallCode($mallCode);

    /**
     * @return string
     */
    public function getOriginalSalesOrganization();

    /**
     * @param string $originalSalesOrganization
     */
    public function setOriginalSalesOrganization($originalSalesOrganization);

    /**
     * @return string
     */
    public function getOriginalMallCode();

    /**
     * @param string $originalMallCode
     */
    public function setOriginalMallCode($originalMallCode);

    /**
     * @return string
     */
    public function getOriginalOrderIncrementId();

    /**
     * @param string $originalOrderIncrementId
     */
    public function setOriginalOrderIncrementId($originalOrderIncrementId);

    /**
     * @return string
     */
    public function getOrderIncrementId();

    /**
     * @param string $orderIncrementId
     */
    public function setOrderIncrementId($orderIncrementId);

    /**
     * @return string
     */
    public function getOrderCreatedAt();

    /**
     * @param string $orderCreatedAt
     */
    public function setOrderCreatedAt($orderCreatedAt);

    /**
     * @return string
     */
    public function getPaymentMethod();

    /**
     * @param string $paymentMethod
     */
    public function setPaymentMethod($paymentMethod);

    /**
     * @return string
     */
    public function getInvoiceCreatedAt();

    /**
     * @param string $invoiceCreatedAt
     */
    public function setInvoiceCreatedAt($invoiceCreatedAt);

    /**
     * @return string
     */
    public function getOrderType();

    /**
     * @param string $orderType
     */
    public function setOrderType($orderType);

    /**
     * @return string
     */
    public function getOrderReasonCode();

    /**
     * @param string $orderReasonCode
     */
    public function setOrderReasonCode($orderReasonCode);

    /**
     * @return string
     */
    public function getOrderReasonText();

    /**
     * @param string $orderReasonText
     */
    public function setOrderReasonText($orderReasonText);

    /**
     * @return string
     */
    public function getUsageIndicator();

    /**
     * @param string $usageIndicator
     */
    public function setUsageIndicator($usageIndicator);

    /**
     * @return string
     */
    public function getCustomerCode();

    /**
     * @param string $customerCode
     */
    public function setCustomerCode($customerCode);

    /**
     * @return string
     */
    public function getCustomerName();

    /**
     * @param string $customerName
     */
    public function setCustomerName($customerName);

    /**
     * @return string
     */
    public function getShippingAddressCode();

    /**
     * @param string $shippingAddressCode
     */
    public function setShippingAddressCode($shippingAddressCode);

    /**
     * @return string
     */
    public function getShippingName();

    /**
     * @param string $shippingName
     */
    public function setShippingName($shippingName);

    /**
     * @return string
     */
    public function getShippingPostcode();

    /**
     * @param string $shippingPostcode
     */
    public function setShippingPostcode($shippingPostcode);

    /**
     * @return string
     */
    public function getShippingRegion();

    /**
     * @param string $shippingRegion
     */
    public function setShippingRegion($shippingRegion);

    /**
     * @return string
     */
    public function getShippingCity();

    /**
     * @param string $shippingCity
     */
    public function setShippingCity($shippingCity);

    /**
     * @return string
     */
    public function getShippingStreet();

    /**
     * @param string $shippingStreet
     */
    public function setShippingStreet($shippingStreet);

    /**
     * @return string
     */
    public function getShippingCountryId();

    /**
     * @param string $shippingCountryId
     */
    public function setShippingCountryId($shippingCountryId);

    /**
     * @return string
     */
    public function getShippingTelephone();

    /**
     * @param string $shippingTelephone
     */
    public function setShippingTelephone($shippingTelephone);

    /**
     * @return string
     */
    public function getOrderCurrencyCode();

    /**
     * @param string $orderCurrencyCode
     */
    public function setOrderCurrencyCode($orderCurrencyCode);

    /**
     * @return float
     */
    public function getSubtotalInclTax();

    /**
     * @param float $subtotalInclTax
     */
    public function setSubtotalInclTax($subtotalInclTax);

    /**
     * @return float
     */
    public function getDiscountAmount();

    /**
     * @param float $discountAmount
     */
    public function setDiscountAmount($discountAmount);

    /**
     * @return float
     */
    public function getGrandTotal();

    /**
     * @param float $grandTotal
     */
    public function setGrandTotal($grandTotal);

    /**
     * @return float
     */
    public function getRewardPointsUsed();

    /**
     * @param float $rewardPointsUsed
     */
    public function setRewardPointsUsed($rewardPointsUsed);

    /**
     * @return float
     */
    public function getShippingAmount();

    /**
     * @param float $shippingAmount
     */
    public function setShippingAmount($shippingAmount);

    /**
     * @return float
     */
    public function getTaxAmount();

    /**
     * @param float $taxAmount
     */
    public function setTaxAmount($taxAmount);

    /**
     * @return string
     */
    public function getShippingAmountPayingSubject();

    /**
     * @param string $shippingAmountPayingSubject
     */
    public function setShippingAmountPayingSubject($shippingAmountPayingSubject);

    /**
     * @return int
     */
    public function getOrderItemCount();

    /**
     * @param int $orderItemCount
     */
    public function setOrderItemCount($orderItemCount);

    /**
     * @return string
     */
    public function getSalesPlant();

    /**
     * @param string $salesPlant
     */
    public function setSalesPlant($salesPlant);

    /**
     * @return string
     */
    public function getSalesStoreLocation();

    /**
     * @param string $salesStoreLocation
     */
    public function setSalesStoreLocation($salesStoreLocation);

    /**
     * @return int
     */
    public function getRmaNo();

    /**
     * @param int $rmaNo
     */
    public function setRmaNo($rmaNo);

    /**
     * @return mixed
     */
    public function getOrderItemData();

    /**
     * @param mixed $orderItemData
     */
    public function setOrderItemData($orderItemData);
}
