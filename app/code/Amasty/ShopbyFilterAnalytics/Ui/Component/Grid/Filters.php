<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Ui\Component\Grid;

use Amasty\ShopbyFilterAnalytics\Model\FunctionalityManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Filters extends \Magento\Ui\Component\Filters
{
    use FunctionalityTrait;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FunctionalityManager $functionalityManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->functionalityManager = $functionalityManager;
    }
}
