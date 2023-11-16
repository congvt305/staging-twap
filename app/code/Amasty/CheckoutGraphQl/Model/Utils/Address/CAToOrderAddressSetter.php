<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Utils\Address;

use Amasty\CheckoutCore\Model\OrderCustomFields;

class CAToOrderAddressSetter
{
    public const CUSTOM_ATTR_KEY = 'custom_attributes';

    public const BILLING_TYPE = 'billing';
    public const SHIPPING_TYPE = 'shipping';

    /**
     * @var CustomAttributesStorage
     */
    private $customAttributesStorage;

    public function __construct(CustomAttributesStorage $customAttributesStorage)
    {
        $this->customAttributesStorage = $customAttributesStorage;
    }

    public function execute(array $result, int $orderId, string $valueType = self::BILLING_TYPE): array
    {
        $orderCustomFields = $this->customAttributesStorage->getData();
        /** @var OrderCustomFields $customField */
        foreach ($orderCustomFields as $customField) {
            if ($customField->getOrderId() == $orderId) {
                if ($valueType === self::BILLING_TYPE) {
                    $value = $customField->getBillingValue();
                } else {
                    $value = $customField->getShippingValue();
                }
                $result[self::CUSTOM_ATTR_KEY][] = [
                    'attribute_code' => $customField->getName(),
                    'value' => $value
                ];
            }
        }

        return $result;
    }
}
