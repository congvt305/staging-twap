<?php
declare(strict_types=1);

namespace CJ\Sms\Model;


class Config
{
    const ENABLED_SMS_VERFICATION = 'cj_sms/general/enabled';

    const LIMIT_PER_DAY = 'cj_sms/general/limit_per_day';

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
     * @param string $path
     * @param string|null $store
     *
     * @return string
     */
    private function getScopeValue($path, $store = null)
    {
        return $this->config->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Is Enabled sms verification
     *
     * @param int|null $store
     *
     * @return bool
     */
    public function isEnabledSmsVerification($store = null)
    {
        return $this->getScopeValue(self::ENABLED_SMS_VERFICATION , $store);
    }


    /**
     * Get limit send sms per day
     *
     * @param int|null $store
     *
     * @return bool
     */
    public function getLimitSendSmsPerDay($store = null)
    {
        return $this->getScopeValue(self::LIMIT_PER_DAY, $store);
    }
}
