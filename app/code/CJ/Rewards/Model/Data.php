<?php
declare(strict_types=1);

namespace CJ\Rewards\Model;

use Amasty\Rewards\Model\Config\Source\RedemptionLimitTypes;
use Amore\PointsIntegration\Model\Config\Source\Actions;
use Laminas\Json\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote;

class Data
{
    const BUNDLE = 'bundle';


    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Amasty\Rewards\Model\Config
     */
    private $amastyConfig;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private  $storeManager;

    /**
     * @param \Magento\Checkout\Model\Session $session
     * @param Config $config
     * @param \Amasty\Rewards\Model\Config $amastyConfig
     * @param TimezoneInterface $timezone
     * @param Json $json
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Checkout\Model\Session $session,
        Config $config,
        \Amasty\Rewards\Model\Config $amastyConfig,
        TimezoneInterface $timezone,
        Json $json,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->session = $session;
        $this->config = $config;
        $this->amastyConfig = $amastyConfig;
        $this->timezone = $timezone;
        $this->json = $json;
        $this->storeManager = $storeManager;
    }

    /**
     * Check can use reward point or not
     *
     * @param Quote $quote
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function canUseRewardPoint($quote = null)
    {
        if (!$this->amastyConfig->isEnabled($quote->getStoreId())) {
            return false;
        }
        if ($this->config->isEnabledRewardsPointForOnlyBundle($quote->getStoreId())) {
            return true;
        }
        if (!$quote) {
            $quote = $this->session->getQuote();
        }

        $isOnlyBundle = true;
        foreach($quote->getAllVisibleItems() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getProductType() != self::BUNDLE) {
                $isOnlyBundle = false;
                break;
            }
        }
        //if only bundle so will hide block, otherwise will show this block
        return $isOnlyBundle ? false : true;
    }

    /**
     * Check whether today can use rewards point or not
     *
     * @return bool|\Magento\Framework\Phrase
     */
    public function isExcludeDay()
    {
        if ($this->config->getExcludeDays()) {
            $currentDate = $this->timezone->date()->format('Y-m-d');
            $excludeDays = $this->json->decode($this->config->getExcludeDays());

            foreach ($excludeDays as $excludeDay) {
                if ($currentDate == $excludeDay->date) {
                    return $excludeDay->content ?? __('current day');
                }
            }
        }
        return false;
    }

    /**
     * Get config can use list reward point or not
     *
     * @param $storeId
     * @return string
     */
    public function isEnableShowListOptionRewardPoint($storeId = null)
    {
        return $this->amastyConfig->isEnabled($storeId) && $this->config->isEnableShowListOptionRewardPoint($storeId);
    }

    /**
     * Get config list reward point
     *
     * @param $storeId
     * @return array
     */
    public function getListOptionRewardPoint($storeId = null)
    {
        if ($this->config->getListOptionRewardPoint($storeId)) {
            $listRewardPoints = $this->json->decode($this->config->getListOptionRewardPoint($storeId));
            $listPoint = [];
            foreach ($listRewardPoints as $rewardPoint) {
                $listPoint[$rewardPoint->point] = $rewardPoint->money;
            }
            ksort($listPoint);


            return $listPoint;
        }
        return [];
    }

    /**
     * Get config is use point or money
     *
     * @param $storeId
     * @return string
     */
    public function isUsePointOrMoney($storeId = null)
    {
        return $this->config->isUsePointOrMoney($storeId);
    }

    /**
     * Get point rate
     *
     * @param $storeId
     * @return float
     */
    public function getPointsRate($storeId = null)
    {
        return $this->amastyConfig->getPointsRate($storeId);
    }

    /**
     * Get store id
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get maximum point
     *
     * @return mixed|string|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMaximumPoint()
    {
        $isEnableLimit = (int)$this->amastyConfig->isEnableLimit($this->getStoreId());

        if ($isEnableLimit === RedemptionLimitTypes::LIMIT_AMOUNT) {
            return $this->amastyConfig->getRewardAmountLimit($this->getStoreId());
        }
    }
}
