<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Model\Source;

class RedirectType implements \Magento\Framework\Data\OptionSourceInterface
{
    public const REDIRECT_301 = 301;
    public const REDIRECT_302 = 302;

    /**
     * @return array|array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::REDIRECT_301, 'label' => __('301 Moved Permanently')],
            ['value' => self::REDIRECT_302, 'label' => __('302 Found')]
        ];
    }
}
