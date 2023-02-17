<?php
declare(strict_types=1);
namespace CJ\CustomCustomer\Model\Source;

/**
 * Class CronExpressions
 */
class CronExpressions
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '*/15 * * * *', 'label' => 'Every 15 Minutes'],
            ['value' => '*/30 * * * *', 'label' => 'Every 30 Minutes'],
            ['value' => '00 * * * *', 'label' => 'Every 60 Minutes'],
            ['value' => '0 0 * * *', 'label' => 'Every 24 Hours'],
        ];
    }
}
