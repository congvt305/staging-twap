<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Model\Filter;

use Amasty\Shopby\Model\Layer\FilterList;
use Amasty\ShopbyFilterAnalytics\Model\SkipFilter\IsSkipFilter;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Resolver;

class GetFiltersForAnalytics
{
    /**
     * @var FilterList
     */
    private $filterList;

    /**
     * @var IsSkipFilter
     */
    private $isSkipFilter;

    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var AbstractFilter[]|null
     */
    private $filters = null;

    public function __construct(
        FilterList $filterList,
        IsSkipFilter $isSkipFilter,
        Resolver $layerResolver
    ) {
        $this->filterList = $filterList;
        $this->isSkipFilter = $isSkipFilter;
        $this->layerResolver = $layerResolver;
    }

    /**
     * @return AbstractFilter[]
     */
    public function execute(): array
    {
        if ($this->filters === null) {
            $this->filters = [];

            foreach ($this->filterList->getAllFilters($this->layerResolver->get()) as $filter) {
                if (!$this->isSkipFilter->execute($filter)) {
                    $this->filters[] = $filter;
                }
            }
        }

        return $this->filters;
    }
}
