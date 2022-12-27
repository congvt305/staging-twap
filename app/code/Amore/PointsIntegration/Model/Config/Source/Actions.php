<?php
declare(strict_types=1);

namespace Amore\PointsIntegration\Model\Config\Source;

class Actions extends \Amasty\Rewards\Model\Config\Source\Actions
{
    const SYSTEM_REWARDS_SYNC = 'system_rewards_sync';
    const ACTION_ADD_POINT = 'add';
    const ACTION_DEDUCT_POINT = 'deduct';

    public function toOptionArray(): array
    {
        $options = parent::toOptionArray();
        $options[self::SYSTEM_REWARDS_SYNC] = __('System Rewards Sync');
        return $options;
    }
}
