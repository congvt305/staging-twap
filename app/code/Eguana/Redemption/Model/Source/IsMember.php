<?php
declare(strict_types=1);

namespace Eguana\Redemption\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
class IsMember implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '1', 'label' => __('Yes')],
            ['value' => '0', 'label' => __('No')]
        ];
    }
}
