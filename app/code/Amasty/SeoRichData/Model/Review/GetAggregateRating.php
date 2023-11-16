<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Model\Review;

use Amasty\SeoRichData\Model\ConfigProvider;
use Amasty\SeoRichData\Model\Review\GetAggregateRating\RatingProviderInterface;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\StoreManagerInterface;

class GetAggregateRating
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
     * @var RatingProviderInterface[]
     */
    private $ratingProviderPool;

    public function __construct(
        StoreManagerInterface $storeManager,
        ConfigProvider $configProvider,
        GetProviderKey $getProviderKey,
        array $ratingProviderPool = []
    ) {
        $this->storeManager = $storeManager;
        $this->configProvider = $configProvider;
        $this->getProviderKey = $getProviderKey;
        $this->ratingProviderPool = $ratingProviderPool;
    }

    /**
     * Get rating rich data for current store.
     *
     * @param Product $product
     * @return array
     */
    public function execute(Product $product): array
    {
        $currentStoreId = (int) $this->storeManager->getStore()->getId();
        $providerKey = $this->getProviderKey->execute($currentStoreId);

        $ratingProvider = $this->ratingProviderPool[$providerKey] ?? null;
        if ($ratingProvider) {
            $rating = $ratingProvider->execute(
                $product,
                $this->configProvider->getRatingFormat($currentStoreId)
            );
        } else {
            $rating = [];
        }

        return $rating;
    }
}
