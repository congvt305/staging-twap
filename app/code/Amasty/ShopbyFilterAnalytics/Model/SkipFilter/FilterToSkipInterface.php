<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Model\SkipFilter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;

interface FilterToSkipInterface
{
    /**
     * Determines whether to collect statistics for the filter
     *
     * @param AbstractFilter $filter
     * @return bool
     */
    public function execute(AbstractFilter $filter): bool;
}
