<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Model\SkipFilter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Eav\Api\Data\AttributeInterface;

class Boolean implements FilterToSkipInterface
{
    public const TYPE_BOOL = 'boolean';

    public function execute(AbstractFilter $filter): bool
    {
        $attributeModel = $filter->getData('attribute_model');

        return $attributeModel && $attributeModel->getData(AttributeInterface::FRONTEND_INPUT) === self::TYPE_BOOL;
    }
}
