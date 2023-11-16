<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Model\SkipFilter;

use Amasty\Shopby\Model\Layer\Filter\Category as CategoryFilter;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

class Category implements FilterToSkipInterface
{
    public function execute(AbstractFilter $filter): bool
    {
        return $filter instanceof CategoryFilter;
    }
}
