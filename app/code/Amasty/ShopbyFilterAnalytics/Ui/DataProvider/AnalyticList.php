<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Ui\DataProvider;

use Amasty\ShopbyFilterAnalytics\Model\ResourceModel\Analytics\FilterCollection;
use Amasty\ShopbyFilterAnalytics\Model\ResourceModel\Analytics\FilterCollectionFactory;
use Amasty\ShopbyFilterAnalytics\Ui\DataProvider\AnalyticsModifier\OptionsProcessor;
use Amasty\ShopbyFilterAnalytics\Ui\DataProvider\AnalyticsModifier\OptionsProcessorFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class AnalyticList extends AbstractDataProvider
{
    public const OPTION_FILTERS = [
        'date'
    ];
    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @var OptionsProcessor
     */
    private $optionsProcessor;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        PoolInterface $pool,
        FilterCollectionFactory $collectionFactory,
        OptionsProcessorFactory $optionsProcessorFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->pool = $pool;
        $this->collection = $collectionFactory->create();
        $this->optionsProcessor = $optionsProcessorFactory->create();
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter): void
    {
        parent::addFilter($filter);
        if (\in_array($filter->getField(), static::OPTION_FILTERS, true)) {
            $this->optionsProcessor->addFilter($filter);
        }
    }

    public function getData(): array
    {
        $data = parent::getData();
        $this->optionsProcessor->modifyData($data);

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $data = $modifier->modifyData($data);
        }

        return $data;
    }

    public function getMeta(): array
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
}
