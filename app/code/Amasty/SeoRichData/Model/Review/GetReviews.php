<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Model\Review;

use Amasty\SeoRichData\Model\ConfigProvider;
use Amasty\SeoRichData\Model\Review\GetReviews\ReviewProviderInterface;
use Magento\Store\Model\StoreManagerInterface;

class GetReviews
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetProviderKey
     */
    private $getProviderKey;

    /**
     * @var ReviewProviderInterface[]
     */
    private $reviewProviderPool;

    public function __construct(
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider,
        GetProviderKey $getProviderKey,
        array $reviewProviderPool = []
    ) {
        $this->storeManager = $storeManager;
        $this->configProvider = $configProvider;
        $this->getProviderKey = $getProviderKey;
        $this->reviewProviderPool = $reviewProviderPool;
    }

    /**
     * Get reviews rich data for current store.
     *
     * @param int $productId
     * @return array
     */
    public function execute(int $productId): array
    {
        $currentStoreId = (int) $this->storeManager->getStore()->getId();
        $providerKey = $this->getProviderKey->execute($currentStoreId);

        $reviewProvider = $this->reviewProviderPool[$providerKey] ?? null;
        if ($reviewProvider) {
            $reviews = $reviewProvider->execute(
                $productId,
                $currentStoreId,
                $this->configProvider->getNumberReviews($currentStoreId),
                $this->configProvider->getRatingFormat($currentStoreId)
            );
        } else {
            $reviews = [];
        }

        return $reviews;
    }
}
