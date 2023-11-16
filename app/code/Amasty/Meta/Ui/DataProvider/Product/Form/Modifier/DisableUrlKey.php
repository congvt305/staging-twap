<?php

declare(strict_types=1);

namespace Amasty\Meta\Ui\DataProvider\Product\Form\Modifier;

use Amasty\Meta\Model\ConfigProvider;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class DisableUrlKey implements ModifierInterface
{
    /**
     * @var LocatorInterface
     */
    private $locator;

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
        ArrayManager $arrayManager,
        ConfigProvider $configProvider
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->configProvider = $configProvider;
    }

    public function modifyMeta(array $meta): array
    {
        if (!$this->locator->getProduct()->getId() && $this->configProvider->isAutomaticallyModifyUrlKey()) {
            $meta = $this->disableUrlKey($meta);
        }

        return $meta;
    }

    public function modifyData(array $data): array
    {
        return $data;
    }
    
    private function disableUrlKey(array $meta): array
    {
        $urlPath = $this->arrayManager->findPath(
            ProductAttributeInterface::CODE_SEO_FIELD_URL_KEY,
            $meta,
            null,
            'children'
        );

        if ($urlPath) {
            $meta = $this->arrayManager->merge(
                $urlPath,
                $meta,
                [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'disabled' => true,
                                'notice' => __('URL Key will be generated via Amasty SEO Meta Tags Templates')
                            ],
                        ],
                    ],
                ]
            );
        }

        return $meta;
    }
}
