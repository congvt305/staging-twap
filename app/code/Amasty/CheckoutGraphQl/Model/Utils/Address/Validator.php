<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Utils\Address;

use Amasty\CheckoutCore\Model\Config;
use Amasty\CheckoutCore\Model\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Store\Model\StoreManagerInterface;

class Validator
{
    public const CUSTOM_FIELD = 'custom_field_';

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var Field
     */
    private $fieldSingleton;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Config $configProvider,
        Field $fieldSingleton,
        StoreManagerInterface $storeManager
    ) {
        $this->configProvider = $configProvider;
        $this->fieldSingleton = $fieldSingleton;
        $this->storeManager = $storeManager;
    }

    /**
     * @param array $addressInput
     * @return void
     * @throws GraphQlInputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validate(array $addressInput): void
    {
        if (!$this->configProvider->isEnabled()) {
            return;
        }

        $fields = $this->fieldSingleton->getConfig($this->storeManager->getStore()->getId());
        /** @var \Amasty\CheckoutCore\Model\Field $field */
        foreach ($fields as $field) {
            $fieldCode = $field->getAttributeCode();
            if ($field->getIsRequired() && empty($addressInput[$fieldCode])) {
                if ($fieldCode === 'country_id') {
                    if (empty($addressInput['country_code'])) {
                        $fieldCode = 'country_code';
                    } else {
                        continue;
                    }
                }

                if ($this->isCustomField($fieldCode)) {
                    continue;
                }
                throw new GraphQlInputException(
                    __(
                        '"%fieldName" is required. Enter and try again.',
                        ['fieldName' => $fieldCode]
                    )
                );
            }
        }
    }

    /**
     * @param string $fieldCode
     * @return bool
     */
    private function isCustomField(string $fieldCode): bool
    {
        if ($fieldCode === self::CUSTOM_FIELD . '1'
            || $fieldCode === self::CUSTOM_FIELD . '2'
            || $fieldCode === self::CUSTOM_FIELD . '3'
        ) {
            return true;
        }

        return false;
    }
}
