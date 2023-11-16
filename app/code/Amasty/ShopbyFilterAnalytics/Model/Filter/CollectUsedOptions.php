<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Model\Filter;

class CollectUsedOptions
{
    /**
     * @var array|null
     */
    private $filterOptions = null;

    /**
     * @var GetFiltersForAnalytics
     */
    private $getFilters;

    public function __construct(
        GetFiltersForAnalytics $getFilters
    ) {
        $this->getFilters = $getFilters;
    }

    public function execute(array $requestParams): array
    {
        if ($this->filterOptions === null) {
            $this->filterOptions = [];
            foreach ($this->getFilters->execute() as $filter) {
                $var = $filter->getRequestVar();
                if (isset($requestParams[$var])) {
                    $options = is_array($requestParams[$var]) ? $requestParams[$var][0] : $requestParams[$var];
                    array_push($this->filterOptions, ...explode(',', $options));
                }
            }
        }

        return $this->filterOptions;
    }
}
