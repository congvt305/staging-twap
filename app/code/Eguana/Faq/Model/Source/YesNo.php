<?php
declare(strict_types=1);

namespace Eguana\Faq\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class YesNo implements OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 1, 'label' => __('Yes')], ['value' => 0, 'label' => __('No')]];
    }
}
