<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Plugin\CustomerGraphQl\Model\Customer\Address\PopulateCustomerAddressFromInput;

use Amasty\CheckoutGraphQl\Model\Utils\Address\CustomAttributesSetter;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\CustomerGraphQl\Model\Customer\Address\PopulateCustomerAddressFromInput;
use Magento\Framework\Api\AttributeInterfaceFactory;

class SetCustomAttributesForResult
{
    /**
     * @var AttributeInterfaceFactory
     */
    private $attributeValueFactory;

    public function __construct(AttributeInterfaceFactory $attributeValueFactory)
    {
        $this->attributeValueFactory = $attributeValueFactory;
    }

    public function afterExecute(
        PopulateCustomerAddressFromInput $subject,
        $result,
        AddressInterface $address,
        array $addressData
    ) {
        if (empty($addressData[CustomAttributesSetter::CUSTOM_ATTR_KEY])) {
            return $result;
        }

        $attributes = [];
        foreach ($addressData[CustomAttributesSetter::CUSTOM_ATTR_KEY] as $attribute) {
            $attributes[] = $this->attributeValueFactory->create()
                ->setAttributeCode($attribute['attribute_code'])
                ->setValue($attribute['value']);
        }
        $address->setData(CustomAttributesSetter::CUSTOM_ATTR_KEY, $attributes);

        return $result;
    }
}
