<?php
declare(strict_types=1);

namespace CJ\Rewards\Model\Points\Converter;

use Amasty\Rewards\Model\Config;
use CJ\Rewards\Model\Data;

class ToMoney extends \Amasty\Rewards\Model\Points\Converter\ToMoney
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Data
     */
    private $rewardData;

    /**
     * @param Config $config
     * @param Data $rewardData
     */
    public function __construct(
        Config $config,
        Data $rewardData
    ) {
        $this->config = $config;
        $this->rewardData = $rewardData;
        parent::__construct($config);
    }

    /**
     * Customize get base point if show list so get amount from list point
     *
     * @param float $points
     * @param int $storeId
     * @param float $allItemsPrice
     * @return float
     */
    public function convert(float $points, int $storeId, float $allItemsPrice): float
    {
        $rate = $this->config->getPointsRate($storeId);
        $roundRule = $this->config->getRoundRule($storeId);
        if($this->rewardData->isEnableShowListOptionRewardPoint($storeId)) {
            $listPoint = $this->rewardData->getListOptionRewardPoint($storeId);
            $basePoints = $listPoint[$points]?? 0;
        } else {
            $basePoints = $points / $rate;
        }


        if ($allItemsPrice < $basePoints) {
            if ($roundRule === 'down') {
                $basePoints = floor($allItemsPrice);
            } else {
                $basePoints = $allItemsPrice;
            }
        }

        return (float)$basePoints;
    }
}
