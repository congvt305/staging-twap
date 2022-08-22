<?php
declare(strict_types=1);

namespace CJ\AmastyCheckoutCore\Plugin\Model;

use Magento\Quote\Api\Data\AddressInterface;

class SetCustomAttribute
{
    const CUSTOM_ATTRIBUTE = [
        'city_id',
        'ward_id',
        'ward'
    ];

    /**
     * Set custom value again to avoid merge custom attribute's value when assign shipping address
     *
     * @param \Amasty\CheckoutCore\Model\QuoteManagement $subject
     * @param $cartId
     * @param AddressInterface|null $shippingAddressFromData
     * @param AddressInterface|null $newCustomerBillingAddress
     * @param $selectedPaymentMethod
     * @param $selectedShippingRate
     * @param $validatedEmailValue
     * @return array
     */
    public function beforeSaveInsertedInfo(
        \Amasty\CheckoutCore\Model\QuoteManagement $subject,
        $cartId,
        AddressInterface $shippingAddressFromData = null,
        AddressInterface $newCustomerBillingAddress = null,
        $selectedPaymentMethod = null,
        $selectedShippingRate = null,
        $validatedEmailValue = null
    ) {
        if ($shippingAddressFromData && $shippingAddressFromData->getCustomAttributes()) {
            foreach($shippingAddressFromData->getCustomAttributes() as $customAttribute) {
                if (in_array($customAttribute->getAttributeCode(), self::CUSTOM_ATTRIBUTE)) {
                    $value = isset($customAttribute->getValue()['value']) ? $customAttribute->getValue()['value'] : $customAttribute->getValue();
                    $shippingAddressFromData->setCustomAttribute($customAttribute->getAttributeCode(), $value);
                }
            }
        }
        if ($newCustomerBillingAddress && $newCustomerBillingAddress->getCustomAttributes()) {
            foreach($newCustomerBillingAddress->getCustomAttributes() as $customAttribute) {
                if (in_array($customAttribute->getAttributeCode(), self::CUSTOM_ATTRIBUTE)) {
                    $value = isset($customAttribute->getValue()['value']) ? $customAttribute->getValue()['value'] : $customAttribute->getValue();
                    $newCustomerBillingAddress->setCustomAttribute($customAttribute->getAttributeCode(), $value);
                }
            }
        }
        return [$cartId, $shippingAddressFromData, $newCustomerBillingAddress, $selectedPaymentMethod, $selectedShippingRate, $validatedEmailValue];
    }
}
