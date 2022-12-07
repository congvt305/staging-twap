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
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param RewardsPropertyProvider $rewardsPropertyProvider
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Amasty\Rewards\Model\Config $configProvider
     * @param Data $rewardData
     * @param \Magento\Checkout\Model\Session $session
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        RewardsPropertyProvider $rewardsPropertyProvider,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Amasty\Rewards\Model\Config $configProvider,
        Data $rewardData,
        \Magento\Checkout\Model\Session $session,
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
        $this->session = $session;
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
     * Get count visible item in quote
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCountQuote()
    {
        return $this->rewardData->countQuote();
    }

    /**
     * Recollect quote so can get correct items data
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function reCollectQuote()
    {
        $this->session->getQuote()->setTotalsCollectedFlag(false)->collectTotals();
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
}
