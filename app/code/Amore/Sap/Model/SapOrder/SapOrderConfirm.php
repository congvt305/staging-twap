<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-12
 * Time: 오후 3:04
 */

namespace Amore\Sap\Model\SapOrder;

use Amore\Sap\Api\Data\SapOrderConfirmInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class SapOrderConfirm extends AbstractExtensibleModel implements SapOrderConfirmInterface
{
    public function getSalesOrganization()
    {
        $this->getData(self::SALES_ORGANIZATION);
    }

    public function setSalesOrganization($salesOrganization)
    {
        $this->setData(self::SALES_ORGANIZATION, $salesOrganization);
    }

    public function getMallCode()
    {
        $this->getData(self::MALL_CODE);
    }

    public function setMallCode($mallCode)
    {
        $this->setData(self::MALL_CODE, $mallCode);
    }

    public function getOriginalSalesOrganization()
    {
        $this->getData(self::ORIGINAL_SALES_ORGANIZATION);
    }

    public function setOriginalSalesOrganization($originalSalesOrganization)
    {
        $this->setData(self::ORIGINAL_SALES_ORGANIZATION, $originalSalesOrganization);
    }

    public function getOriginalMallCode()
    {
        $this->getData(self::ORIGINAL_MALL_CODE);
    }

    public function setOriginalMallCode($originalMallCode)
    {
        $this->setData(self::ORIGINAL_MALL_CODE, $originalMallCode);
    }

    public function getOriginalOrderIncrementId()
    {
        $this->getData(self::ORIGINAL_ORDER_INCREMENT_ID);
    }

    public function setOriginalOrderIncrementId($originalOrderIncrementId)
    {
        $this->setData(self::ORIGINAL_ORDER_INCREMENT_ID, $originalOrderIncrementId);
    }

    public function getOrderIncrementId()
    {
        $this->getData(self::ORDER_INCREMENT_ID);
    }

    public function setOrderIncrementId($orderIncrementId)
    {
        $this->setData(self::ORDER_INCREMENT_ID, $orderIncrementId);
    }

    public function getOrderCreatedAt()
    {
        $this->getData(self::ORDER_CREATED_AT);
    }

    public function setOrderCreatedAt($orderCreatedAt)
    {
        $this->setData(self::ORDER_CREATED_AT, $orderCreatedAt);
    }

    public function getPaymentMethod()
    {
        $this->getData(self::PAYMENT_METHOD);
    }

    public function setPaymentMethod($paymentMethod)
    {
        $this->setData(self::PAYMENT_METHOD, $paymentMethod);
    }

    public function getInvoiceCreatedAt()
    {
        $this->getData(self::INVOICE_CREATED_AT);
    }

    public function setInvoiceCreatedAt($invoiceCreatedAt)
    {
        $this->setData(self::INVOICE_CREATED_AT, $invoiceCreatedAt);
    }

    public function getOrderType()
    {
        $this->getData(self::ORDER_TYPE);
    }

    public function setOrderType($orderType)
    {
        $this->setData(self::ORDER_TYPE, $orderType);
    }

    public function getOrderReasonCode()
    {
        $this->getData(self::ORDER_REASON_CODE);
    }

    public function setOrderReasonCode($orderReasonCode)
    {
        $this->setData(self::ORDER_REASON_CODE, $orderReasonCode);
    }

    public function getOrderReasonText()
    {
        $this->getData(self::ORDER_REASON_TEXT);
    }

    public function setOrderReasonText($orderReasonText)
    {
        $this->setData(self::ORDER_REASON_TEXT, $orderReasonText);
    }

    public function getUsageIndicator()
    {
        $this->getData(self::USAGE_INDICATOR);
    }

    public function setUsageIndicator($usageIndicator)
    {
        $this->setData(self::USAGE_INDICATOR, $usageIndicator);
    }

    public function getCustomerCode()
    {
        $this->getData(self::CUSTOMER_CODE);
    }

    public function setCustomerCode($customerCode)
    {
        $this->setData(self::CUSTOMER_CODE, $customerCode);
    }

    public function getCustomerName()
    {
        $this->getData(self::CUSTOMER_NAME);
    }

    public function setCustomerName($customerName)
    {
        $this->setData(self::CUSTOMER_NAME, $customerName);
    }

    public function getShippingAddressCode()
    {
        $this->getData(self::SHIPPING_ADDRESS_CODE);
    }

    public function setShippingAddressCode($shippingAddressCode)
    {
        $this->setData(self::SHIPPING_ADDRESS_CODE, $shippingAddressCode);
    }

    public function getShippingName()
    {
        $this->getData(self::SHIPPING_NAME);
    }

    public function setShippingName($shippingName)
    {
        $this->setData(self::SHIPPING_NAME, $shippingName);
    }

    public function getShippingPostcode()
    {
        $this->getData(self::SHIPPING_POSTCODE);
    }

    public function setShippingPostcode($shippingPostcode)
    {
        $this->setData(self::SHIPPING_POSTCODE, $shippingPostcode);
    }

    public function getShippingRegion()
    {
        $this->getData(self::SHIPPING_REGION);
    }

    public function setShippingRegion($shippingRegion)
    {
        $this->setData(self::SHIPPING_REGION, $shippingRegion);
    }

    public function getShippingCity()
    {
        $this->getData(self::SHIPPING_CITY);
    }

    public function setShippingCity($shippingCity)
    {
        $this->setData(self::SHIPPING_CITY, $shippingCity);
    }

    public function getShippingStreet()
    {
        $this->getData(self::SHIPPING_STREET);
    }

    public function setShippingStreet($shippingStreet)
    {
        $this->setData(self::SHIPPING_STREET, $shippingStreet);
    }

    public function getShippingCountryId()
    {
        $this->getData(self::SHIPPING_COUNTRY_ID);
    }

    public function setShippingCountryId($shippingCountryId)
    {
        $this->setData(self::SHIPPING_COUNTRY_ID, $shippingCountryId);
    }

    public function getShippingTelephone()
    {
        $this->getData(self::SHIPPING_TELEPHONE);
    }

    public function setShippingTelephone($shippingTelephone)
    {
        $this->setData(self::SHIPPING_TELEPHONE, $shippingTelephone);
    }

    public function getOrderCurrencyCode()
    {
        $this->getData(self::ORDER_CURRENCY_CODE);
    }

    public function setOrderCurrencyCode($orderCurrencyCode)
    {
        $this->setData(self::ORDER_CURRENCY_CODE, $orderCurrencyCode);
    }

    public function getSubtotalInclTax()
    {
        $this->getData(self::SUBTOTAL_INCL_TAX);
    }

    public function setSubtotalInclTax($subtotalInclTax)
    {
        $this->setData(self::SUBTOTAL_INCL_TAX, $subtotalInclTax);
    }

    public function getDiscountAmount()
    {
        $this->getData(self::DISCOUNT_AMOUNT);
    }

    public function setDiscountAmount($discountAmount)
    {
        $this->setData(self::DISCOUNT_AMOUNT, $discountAmount);
    }

    public function getGrandTotal()
    {
        $this->getData(self::GRAND_TOTAL);
    }

    public function setGrandTotal($grandTotal)
    {
        $this->setData(self::GRAND_TOTAL, $grandTotal);
    }

    public function getRewardPointsUsed()
    {
        $this->getData(self::REWARD_POINTS_USED);
    }

    public function setRewardPointsUsed($rewardPointsUsed)
    {
        $this->setData(self::REWARD_POINTS_USED, $rewardPointsUsed);
    }

    public function getShippingAmount()
    {
        $this->getData(self::SHIPPING_AMOUNT);
    }

    public function setShippingAmount($shippingAmount)
    {
        $this->setData(self::SHIPPING_AMOUNT, $shippingAmount);
    }

    public function getTaxAmount()
    {
        $this->getData(self::TAX_AMOUNT);
    }

    public function setTaxAmount($taxAmount)
    {
        $this->setData(self::TAX_AMOUNT, $taxAmount);
    }

    public function getShippingAmountPayingSubject()
    {
        $this->getData(self::SHIPPING_AMOUNT_PAYING_SUBJECT);
    }

    public function setShippingAmountPayingSubject($shippingAmountPayingSubject)
    {
        $this->setData(self::SHIPPING_AMOUNT_PAYING_SUBJECT, $shippingAmountPayingSubject);
    }

    public function getOrderItemCount()
    {
        $this->getData(self::ORDER_ITEM_COUNT);
    }

    public function setOrderItemCount($orderItemCount)
    {
        $this->setData(self::ORDER_ITEM_COUNT, $orderItemCount);
    }

    public function getSalesPlant()
    {
        $this->getData(self::SALES_PLANT);
    }

    public function setSalesPlant($salesPlant)
    {
        $this->setData(self::SALES_PLANT, $salesPlant);
    }

    public function getSalesStoreLocation()
    {
        $this->getData(self::SALES_STORE_LOCATION);
    }

    public function setSalesStoreLocation($salesStoreLocation)
    {
        $this->setData(self::SALES_STORE_LOCATION, $salesStoreLocation);
    }

    public function getRmaNo()
    {
        $this->getData(self::RMA_NO);
    }

    public function setRmaNo($rmaNo)
    {
        $this->setData(self::RMA_NO, $rmaNo);
    }

    public function getOrderItemData()
    {
        $this->getData(self::ORDER_ITEM_DATA);
    }

    public function setOrderItemData($orderItemData)
    {
        $this->setData(self::ORDER_ITEM_DATA, $orderItemData);
    }
}
