<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Utils\Address;

class CustomAttributesSetter
{
    public const CUSTOM_ATTR_KEY = 'custom_attributes';

    /**
     * @param array $address
     * @return array
     */
    public function execute(array $address): array
    {
        if (isset($address[self::CUSTOM_ATTR_KEY])) {
            $customAttributes = $address[self::CUSTOM_ATTR_KEY];
            foreach ($customAttributes as $attribute) {
                $address[$attribute['attribute_code']] = $attribute['value'];
            }
        }

        return $address;
    }
}
