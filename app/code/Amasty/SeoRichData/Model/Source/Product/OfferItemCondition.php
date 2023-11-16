<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Model\Source\Product;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class OfferItemCondition extends AbstractSource
{
    public const ATTRIBUTE_CODE = 'am_offer_item_condition';

    public const DAMAGED_CONDITION = 0;
    public const NEW_CONDITION = 1;
    public const REFURBISHED_CONDITION = 2;
    public const USED_CONDITION = 3;

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getAllOptions()
    {
        return [
            ['value' => self::DAMAGED_CONDITION, 'label' => __('DamagedCondition')],
            ['value' => self::NEW_CONDITION, 'label' => __('NewCondition')],
            ['value' => self::REFURBISHED_CONDITION, 'label' => __('RefurbishedCondition')],
            ['value' => self::USED_CONDITION, 'label' => __('UsedCondition')]
        ];
    }

    public function getConditionValue(int $value): string
    {
        switch ($value) {
            case self::DAMAGED_CONDITION:
                $result = 'https://schema.org/DamagedCondition';
                break;
            case self::REFURBISHED_CONDITION:
                $result = 'https://schema.org/RefurbishedCondition';
                break;
            case self::USED_CONDITION:
                $result = 'https://schema.org/UsedCondition';
                break;
            default:
                $result = 'https://schema.org/NewCondition';
        }

        return $result;
    }
}
