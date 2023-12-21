<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Ui\DataProvider\AnalyticsModifier;

use Amasty\ShopbyFilterAnalytics\Model\DateConverter;
use Amasty\ShopbyFilterAnalytics\Model\FunctionalityManager;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Default date filter for statistic grid
 */
class DateFilterModifier implements ModifierInterface
{
    /**
     * @var DateConverter
     */
    private $dateConverter;

    /**
     * @var FunctionalityManager
     */
    private $functionalityManager;

    public function __construct(DateConverter $dateConverter, FunctionalityManager $functionalityManager)
    {
        $this->dateConverter = $dateConverter;
        $this->functionalityManager = $functionalityManager;
    }

    public function modifyData(array $data): array
    {
        return $data;
    }

    public function modifyMeta(array $meta): array
    {
        if ($this->functionalityManager->isPremActive()) {
            $meta['listing_top']['children']['statistic_filter']['arguments']['data']['config']['applied']['date'] = [
                'from' => $this->dateConverter->getOutputDate('-14day'),
                'to' => $this->dateConverter->getOutputDate()
            ];
        }

        return $meta;
    }
}
