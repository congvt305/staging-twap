<?php

declare(strict_types=1);

namespace Amasty\ShopByQuickConfig\Model\Source;

use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\LayeredNavigation\Model\Attribute\Source\FilterableOptions;

class NavigationUsage implements OptionSourceInterface
{
    /**
     * @var FilterableOptions
     */
    private $filterableOptions;

    /**
     * @var Yesno
     */
    private $yesNo;

    public function __construct(FilterableOptions $filterableOptions, Yesno $yesNo)
    {
        $this->filterableOptions = $filterableOptions;
        $this->yesNo = $yesNo;
    }

    public function toOptionArray(): array
    {
        return [
            '0' => $this->filterableOptions->toOptionArray(),
            '1' => $this->yesNo->toOptionArray()
        ];
    }
}
