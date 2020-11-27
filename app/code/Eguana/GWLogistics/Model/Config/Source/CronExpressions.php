<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/19/20
 * Time: 12:47 PM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Config\Source;

class CronExpressions implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '*/15 * * * *', 'label' => 'Every 15 Minutes'],
            ['value' => '*/30 * * * *', 'label' => 'Every 30 Minutes'],
            ['value' => '00 * * * *', 'label' => 'Every 60 Minutes'],
        ];
    }
}