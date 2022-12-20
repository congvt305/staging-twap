<?php
declare(strict_types=1);

namespace CJ\Rewards\Model;

use Amasty\Rewards\Model\Config\Source\RedemptionLimitTypes;
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
        if (!$this->amastyConfig->isEnabled()) {
            return false;
        }
        if ($this->config->isEnabledRewardsPointForOnlyBundle()) {
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
     * Count visible item in quote
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function countQuote() {
        $quote = $this->session->getQuote();
        return count($quote->getAllVisibleItems());
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
