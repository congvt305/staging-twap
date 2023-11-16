<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Plugin\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;

use Amasty\SeoRichData\Model\Source\Product\OfferItemCondition;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav as EavModifier;

class ModifyOfferConditionAttribute
{
    public function afterSetupAttributeMeta(
        EavModifier $eavModifier,
        array $meta,
        ProductAttributeInterface $attribute
    ): array {
        if ($attribute->getAttributeCode() === OfferItemCondition::ATTRIBUTE_CODE) {
            $meta['arguments']['data']['config']['additionalInfo'] = __(
                'Defines the product’s condition (<a href="%1" target="_blank">%2</a>).
                 Please make sure the Show Condition setting
                 is enabled under Stores-Configuration-Amasty SEO Rich Data-Product Rich Data fieldset.',
                'https://schema.org/OfferItemCondition',
                'https://schema.org/OfferItemCondition'
            );
            $meta['arguments']['data']['config']['default'] = $attribute->getDefaultValue();
        }

        return $meta;
    }
}
