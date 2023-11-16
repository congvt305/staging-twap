<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Ui\DataProvider\Product\Modifier;

use Amasty\SeoRichData\Model\Backend\Preview\GetRichData;
use Amasty\SeoRichData\Model\ConfigProvider;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class AddPreviewData implements ModifierInterface
{
    private const PREVIEW_DATA_SCOPE = '%d/product/rich_data_preview';

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var GetRichData
     */
    private $getRichData;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        LocatorInterface $locator,
        GetRichData $getRichData,
        ArrayManager $arrayManager,
        ConfigProvider $configProvider
    ) {
        $this->locator = $locator;
        $this->getRichData = $getRichData;
        $this->arrayManager = $arrayManager;
        $this->configProvider = $configProvider;
    }

    public function modifyData(array $data): array
    {
        $storeId = (int) $this->locator->getStore()->getId();
        if ($this->configProvider->isEnabledForProduct($storeId) && $this->locator->getProduct()->getId()) {
            $previewRichData = $this->getRichData->execute($this->locator->getProduct(), $storeId);
            $data = $this->arrayManager->set(
                sprintf(self::PREVIEW_DATA_SCOPE, $this->locator->getProduct()->getId()),
                $data,
                $previewRichData
            );
        }

        return $data;
    }

    public function modifyMeta(array $meta): array
    {
        if (!$this->configProvider->isEnabledForProduct((int) $this->locator->getStore()->getId())) {
            $meta = $this->arrayManager->set(
                'search-engine-optimization/children/rich_data_preview/arguments/data/config',
                $meta,
                ['visible' => false]
            );
        }

        return $meta;
    }
}
