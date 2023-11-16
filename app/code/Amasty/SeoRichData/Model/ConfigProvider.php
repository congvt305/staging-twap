<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    public const STREET_ADDRESS_PATH = 'organization/street';
    public const PRODUCT_ENABLED = 'product/enabled';
    public const RATING_FORMAT_PATH = 'product/rating_format';
    public const PRICE_VALID_DEFAULT_PATH = 'product/price_valid_until';
    public const PRICE_VALID_REPLACE_PATH = 'product/replace_price_valid_until';
    public const NUMBER_OF_REVIEWS = 'product/number_reviews';
    public const USE_YOTPO = 'product/use_yotpo';
    public const PRODUCT_DESCRIPTION_MODE = 'product/description';
    public const PRODUCT_SHOW_RATING = 'product/rating';
    public const PRODUCT_SHOW_AVAILABILITY = 'product/availability';

    /**
     * @var string
     */
    protected $pathPrefix = 'amseorichdata/';

    public function getStreetAddress(): string
    {
        return (string) $this->getValue(self::STREET_ADDRESS_PATH);
    }

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getRatingFormat(?int $storeId = null): int
    {
        return (int) $this->getValue(self::RATING_FORMAT_PATH, $storeId);
    }

    public function getDefaultPriceValidUntil(): string
    {
        return (string) $this->getValue(self::PRICE_VALID_DEFAULT_PATH);
    }

    public function isReplacePriceValidUntil(): bool
    {
        return $this->isSetFlag(self::PRICE_VALID_REPLACE_PATH);
    }

    /**
     * Get number of reviews for display in rich data.
     * If empty - show all.
     *
     * @param int|null $storeId
     * @return int
     */
    public function getNumberReviews(?int $storeId = null): int
    {
        return (int) $this->getValue(self::NUMBER_OF_REVIEWS, $storeId);
    }

    /**
     * @param null|int $storeId
     * @return bool
     */
    public function isUseYotpo(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::USE_YOTPO, $storeId);
    }

    public function getProductDescriptionMode(?int $storeId = null): int
    {
        return (int) $this->getValue(self::PRODUCT_DESCRIPTION_MODE, $storeId);
    }

    public function isShowRating(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::PRODUCT_SHOW_RATING, $storeId);
    }

    public function isShowAvailability(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::PRODUCT_SHOW_AVAILABILITY, $storeId);
    }

    public function isEnabledForProduct(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::PRODUCT_ENABLED, $storeId);
    }
}
