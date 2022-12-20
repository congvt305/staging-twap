<?php
declare(strict_types=1);

namespace CJ\Rewards\Model\Quote\Validator;

use Amasty\Rewards\Model\Config;
use Amasty\Rewards\Model\Config\Source\RedemptionLimitTypes;
use Magento\Quote\Model\Quote;

class LimitValidator extends \Amasty\Rewards\Model\Quote\Validator\LimitValidator
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @param Config $configProvider
     */
    public function __construct(
        Config $configProvider
    ) {
        $this->configProvider = $configProvider;
        parent::__construct($configProvider);
    }

    /**
     * Validate before add point to cart
     *
     * @param Quote $quote
     * @param float|int|string $usedPoints
     * @param array &$pointsData
     */
    public function validate(Quote $quote, $usedPoints, array &$pointsData): void
    {
        $storeId = (int)$quote->getStoreId();
        $pointsData['allowed_points'] = $usedPoints;
        $isEnableLimit = (int)$this->configProvider->isEnableLimit($storeId);
        //Customize here
        $rate = $this->configProvider->getPointsRate($storeId);
        if ($usedPoints % $rate != 0) {
            $multiple = $usedPoints / $rate;
            $pointsData['allowed_points'] = (int)$multiple * $rate;
            $pointsData['notice'] =
                __('Number of redeemed reward points must be multiple of %1 for this order.', $rate);
            //End of customize
        } elseif ($isEnableLimit === RedemptionLimitTypes::LIMIT_AMOUNT) {

            $limitAmount = $this->configProvider->getRewardAmountLimit($storeId);

            if ($usedPoints > $limitAmount) {
                $pointsData['allowed_points'] = $limitAmount;
                $pointsData['notice'] =
                    __('Number of redeemed reward points cannot exceed %1 for this order.', $limitAmount);
            }
        } elseif ($isEnableLimit === RedemptionLimitTypes::LIMIT_PERCENT) {
            $limitPercent = $this->configProvider->getRewardPercentLimit($storeId);
            $subtotal = $quote->getSubtotal();
            $allowedPercent = round(($subtotal / 100 * $limitPercent) / $quote->getBaseToQuoteRate(), 2);
            $rate = $this->configProvider->getPointsRate($storeId);
            $basePoints = $usedPoints / $rate;

            if ($basePoints > $allowedPercent) {
                $pointsData['allowed_points'] = $allowedPercent * $rate;
                $pointsData['notice'] =
                    __('Number of redeemed reward points cannot exceed %1 '
                        . '% of cart subtotal excluding tax for this order.', $limitPercent);
            }
        }
    }
}
