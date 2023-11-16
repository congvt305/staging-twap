<?php

declare(strict_types=1);

namespace Amasty\Shopby\Plugin\Catalog;

use Amasty\Shopby\Helper\FilterSetting as FilterSettingHelper;
use Amasty\Shopby\Model\Source\DisplayMode;
use Closure;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Swatches\Helper\Data;

class Swatches
{
    /**
     * @var FilterSettingHelper
     */
    private $filterSettingHelper;

    public function __construct(
        FilterSettingHelper $filterSettingHelper
    ) {
        $this->filterSettingHelper = $filterSettingHelper;
    }

    public function aroundIsSwatchAttribute(
        Data $subject,
        Closure $closure,
        Attribute $attribute
    ): bool {
        $isSwatchAttribute = $closure($attribute);
        if (!$isSwatchAttribute) {
            $filterSetting = $this->filterSettingHelper->getSettingByAttribute($attribute);
            $isSwatchAttribute = $filterSetting && in_array(
                $filterSetting->getDisplayMode(),
                [DisplayMode::MODE_IMAGES_LABELS, DisplayMode::MODE_IMAGES]
            );
        }

        return $isSwatchAttribute;
    }
}
