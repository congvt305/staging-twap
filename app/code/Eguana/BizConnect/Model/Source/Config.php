<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/09/07
 * Time: 1:03 PM
 */

namespace Eguana\BizConnect\Model\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const BIZCONNECT_LOG_DELETE_CRON_ACTIVE = 'eguana_bizconnect/configurable_cron/active';
    const BIZCONNECT_LOG_DELETE_CRON_DAYS_TO_DELETE = 'eguana_bizconnect/configurable_cron/days_to_delete';
    const BIZCONNECT_LOG_DELETE_CRON_NUMBERS_TO_DELETE = 'eguana_bizconnect/configurable_cron/numbers_to_delete';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getActive($storeId)
    {
        return $this->scopeConfig->getValue(self::BIZCONNECT_LOG_DELETE_CRON_ACTIVE, 'store', $storeId);
    }

    public function getDaysToDelete($storeId)
    {
        return $this->scopeConfig->getValue(self::BIZCONNECT_LOG_DELETE_CRON_DAYS_TO_DELETE, 'store', $storeId);
    }

    public function getNumbersToDelete($storeId)
    {
        return $this->scopeConfig->getValue(self::BIZCONNECT_LOG_DELETE_CRON_NUMBERS_TO_DELETE, 'store', $storeId);
    }
}
