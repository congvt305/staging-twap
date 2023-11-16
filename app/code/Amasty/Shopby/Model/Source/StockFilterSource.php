<?php

namespace Amasty\Shopby\Model\Source;

class StockFilterSource implements \Magento\Framework\Option\ArrayInterface
{
    public const STOCK_STATUS = 'stock_status';
    public const QTY = 'qty';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::STOCK_STATUS,
                'label' => __('Disabled stock status')
            ],
            [
                'value' => self::QTY,
                'label' => __('Quantity threshold')
            ]
        ];
    }
}
