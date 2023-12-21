<?php

declare(strict_types=1);

namespace Amasty\Shopby\Model;

use Magento\Store\Model\ScopeInterface;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    public const TOOLTIPS_ENABLED = 'tooltips/enabled';
    public const TOOLTIP_IMAGE = 'tooltips/image';
    public const KEEP_SINGLE_CHOICE_VISIBLE = 'general/keep_single_choice_visible';
    public const STOCK_SOURCE = 'stock_filter/stock_source';
    public const STOCK_FILTER_ENABLED = 'stock_filter/enabled';
    public const RATING_FILTER_ENABLED = 'rating_filter/enabled';
    public const IS_NEW_FILTER_ENABLED = 'am_is_new_filter/enabled';
    public const ON_SALE_FILTER_ENABLED = 'am_on_sale_filter/enabled';
    public const EXCLUDE_OUT_OF_STOCK = 'general/exclude_out_of_stock';
    public const BRAND_ATTRIBUTE_CODE = 'amshopby_brand/general/attribute_code';
    public const ENABLE_OVERFLOW_SCROLL = 'general/enable_overflow_scroll';
    public const SLIDER_STYLE = 'slider/slider_style';
    public const SLIDER_COLOR = 'slider/slider_color';

    /**
     * @var string
     */
    protected $pathPrefix = 'amshopby/';

    public function isTooltipsEnabled(): string
    {
        return (string)$this->getValue(self::TOOLTIPS_ENABLED);
    }

    public function isEnableOverflowScroll(): bool
    {
        return (bool) $this->getValue(self::ENABLE_OVERFLOW_SCROLL);
    }

    public function getOverflowScrollValue(): int
    {
        return (int) $this->getValue(self::ENABLE_OVERFLOW_SCROLL);
    }

    public function getTooltipSrc(): string
    {
        return (string)$this->getValue(self::TOOLTIP_IMAGE);
    }

    public function isSingleChoiceMode(): bool
    {
        return $this->isSetFlag(self::KEEP_SINGLE_CHOICE_VISIBLE);
    }

    public function getStockSource(): string
    {
        return $this->getValue(self::STOCK_SOURCE);
    }

    public function isStockFilterEnabled(): bool
    {
        return $this->isSetFlag(self::STOCK_FILTER_ENABLED);
    }

    public function isRatingFilterEnabled(): bool
    {
        return $this->isSetFlag(self::RATING_FILTER_ENABLED);
    }

    public function isNewFilterEnabled(): bool
    {
        return $this->isSetFlag(self::IS_NEW_FILTER_ENABLED);
    }

    public function isSaleFilterEnabled(): bool
    {
        return $this->isSetFlag(self::ON_SALE_FILTER_ENABLED);
    }

    public function getBrandAttributeCode(): string
    {
        return (string) $this->getGlobalValue(self::BRAND_ATTRIBUTE_CODE);
    }

    public function isExcludeOutOfStock(): bool
    {
        return (bool)$this->getValue(self::EXCLUDE_OUT_OF_STOCK);
    }

    public function getStockConfig(): array
    {
        return $this->getValue('stock_filter');
    }

    public function getRatingConfig(): array
    {
        return $this->getValue('rating_filter');
    }

    public function getNewConfig(): array
    {
        return $this->getValue('am_is_new_filter');
    }

    public function getOnSaleConfig(): array
    {
        return $this->getValue('am_on_sale_filter');
    }

    public function getSliderStyle(?int $storeId = null, ?string $scope = ScopeInterface::SCOPE_STORE): string
    {
        return (string) $this->getValue(self::SLIDER_STYLE, $storeId, $scope);
    }

    public function getSliderColor(?int $storeId = null, ?string $scope = ScopeInterface::SCOPE_STORE): string
    {
        return (string) $this->getValue(self::SLIDER_COLOR, $storeId, $scope);
    }
}
