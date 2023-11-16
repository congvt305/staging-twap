<?php

declare(strict_types=1);

namespace Amasty\Shopby\Block\Navigation;

use Amasty\Shopby\Model\Layer\GetFiltersExpanded;
use Magento\Framework\View\Element\Template\Context;

class FilterCollapsing extends \Magento\Framework\View\Element\Template
{
    /**
     * @var GetFiltersExpanded
     */
    private $getFiltersExpanded;

    public function __construct(
        GetFiltersExpanded $getFiltersExpanded,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->getFiltersExpanded = $getFiltersExpanded;
    }

    /**
     * @return int[]
     */
    public function getFiltersExpanded(): array
    {
        return $this->getFiltersExpanded->execute();
    }
}
