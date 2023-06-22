<?php
declare(strict_types=1);

namespace CJ\Rewards\Block\Frontend\Cart;

use Amasty\Rewards\Model\RewardsPropertyProvider;
use CJ\Rewards\Model\Data;

class Rewards extends \Amasty\Rewards\Block\Frontend\Cart\Rewards
{
    /**
     * @var Data
     */
    private $rewardData;


    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param RewardsPropertyProvider $rewardsPropertyProvider
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Amasty\Rewards\Model\Config $configProvider
     * @param Data $rewardData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        RewardsPropertyProvider $rewardsPropertyProvider,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Amasty\Rewards\Model\Config $configProvider,
        Data $rewardData,
        array $data
    ) {
        parent::__construct(
            $context,
            $rewardsPropertyProvider,
            $priceCurrency,
            $configProvider,
            $data
        );
        $this->rewardData = $rewardData;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function canShowConvertPoint()
    {
        return $this->rewardData->canUseRewardPoint();
    }

    /**
     * Get maximum point
     *
     * @return mixed|string|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMaximumPoint()
    {
        return $this->rewardData->getMaximumPoint();
    }

    /**
     * Get config is use point or money
     *
     * @return string
     */
    public function isUsePointOrMoney()
    {
        return $this->rewardData->isUsePointOrMoney();
    }
}
