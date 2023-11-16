<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Model;

use Amasty\ShopbyFilterAnalytics\Model\Filter\CollectUsedOptions;

class CollectGraphQlFilterData
{
    public const EQUAL = 'eq';
    public const IN = 'in';

    /**
     * @var ProcessAnalytics
     */
    private $processAnalytics;

    /**
     * @var CollectUsedOptions
     */
    private $collectUsedOptions;

    public function __construct(
        ProcessAnalytics $processAnalytics,
        CollectUsedOptions $collectUsedOptions
    ) {
        $this->processAnalytics = $processAnalytics;
        $this->collectUsedOptions = $collectUsedOptions;
    }

    public function execute(array $filters, ?int $categoryId): void
    {
        $options = $this->collectUsedOptions->execute($this->getOptions($filters));
        $this->processAnalytics->execute($options, $categoryId);
    }

    private function getOptions(array $filters): array
    {
        $options = [];
        foreach ($filters as $filter => $option) {
            if (isset($option[self::EQUAL])) {
                $options[$filter] = $option[self::EQUAL];
            }
            if (isset($option[self::IN])) {
                array_push($options, ...$option[self::IN]);
            }
        }

        return $options;
    }
}
