<?php
declare(strict_types=1);

namespace CJ\Rewards\Model;

use \Amasty\Rewards\Model\Config as AmastyConfig;

class Config
{
    const ENABLED_REWARDS_POINT_FOR_ONLY_BUNDLE = 'enabled_rewards_point_for_only_bundle';

    const EXCLUDE_DAYS = 'exclude_days';

    const POINT_OR_MONEY = 'use_point_or_money';
    const LIST_OPTION_REWARD_POINT = 'list_option_reward_point';

    const ENABLED_SHOW_LIST_OPTION_REWARD_POINT = 'enabled_show_list_option_reward_point';

    const USE_POINT_TO_GET_DISCOUNT = 1;

    const USE_MONEY_TO_GET_DISCOUNT = 2;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Get scope value
     *
     * @param string $group
     * @param string $path
     * @param string|null $store
     *
     * @return string
     */
    private function getScopeValue($group, $path, $store = null)
    {
        return $this->config->getValue(
            AmastyConfig::REWARDS_SECTION . $group . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Is Enabled rewards point for only bundle
     *
     * @param int|null $store
     *
     * @return bool
     */
    public function isEnabledRewardsPointForOnlyBundle($store = null)
    {
        return $this->getScopeValue(AmastyConfig::GENERAL_GROUP, self::ENABLED_REWARDS_POINT_FOR_ONLY_BUNDLE , $store);
    }


    /**
     * Get exclude day cannot use convert point
     *
     * @param int|null $store
     *
     * @return bool
     */
    public function getExcludeDays($store = null)
    {
        return $this->getScopeValue(AmastyConfig::GENERAL_GROUP, self::EXCLUDE_DAYS, $store);
    }

    /**
     * Get config use point or money to get discount
     *
     * @param $store
     * @return string
     */
    public function isUsePointOrMoney($store = null)
    {
        return $this->getScopeValue(AmastyConfig::GENERAL_GROUP, self::POINT_OR_MONEY, $store);
    }

    /**
     * Get list reward point
     *
     * @param $store
     * @return string
     */
    public function getListOptionRewardPoint($store = null)
    {
        return $this->getScopeValue(AmastyConfig::GENERAL_GROUP, self::LIST_OPTION_REWARD_POINT, $store);
    }

    /**
     * Is enable show list option
     *
     * @param $store
     * @return string
     */
    public function isEnableShowListOptionRewardPoint($store = null)
    {
        return $this->getScopeValue(AmastyConfig::GENERAL_GROUP, self::ENABLED_SHOW_LIST_OPTION_REWARD_POINT, $store);
    }
}
