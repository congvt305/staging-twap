<?php
declare(strict_types=1);

namespace CJ\Rewards\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class PointOrMoney implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 1, 'label' => __('Point')], ['value' => 2, 'label' => __('Money')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [1 => __('Point'), 2 => __('Money')];
    }
}
