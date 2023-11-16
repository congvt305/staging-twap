<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Model\Review;

use Amasty\SeoRichData\Model\ConfigProvider;

/**
 * Provide provider key for detect review source.
 * supported:
 * - default (magento)
 * - yotpo
 */
class GetProviderKey
{
    public const DEFAULT_PROVIDER_KEY = 'default';
    public const YOTPO_PROVIDER_KEY = 'yotpo';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function execute(int $storeId): string
    {
        if ($this->configProvider->isUseYotpo($storeId)) {
            $providerKey = self::YOTPO_PROVIDER_KEY;
        } else {
            $providerKey = self::DEFAULT_PROVIDER_KEY;
        }

        return $providerKey;
    }
}
