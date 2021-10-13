<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2021-01-27
 * Time: 오전 11:00
 */

namespace Eguana\InventoryCompensation\Model\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const INVENTORY_COMPENSATION_ACTIVE_XML_PATH = 'inventory_compensation/general/active';

    const INVENTORY_COMPENSATION_LOGGER_ACTIVE_XML_PATH = 'inventory_compensation/general/logger_active';

    const CLEAN_RESERVATION_NUMBERS_ORDERS_CLEAN = 'inventory_compensation/inventory_compensation_cron/numbers_orders_clean';

    const CLEAN_RESERVATION_STATUSES_TO_CLEAN = 'inventory_compensation/inventory_compensation_cron/statuses_clean';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getActive()
    {
        return $this->scopeConfig->getValue(self::INVENTORY_COMPENSATION_ACTIVE_XML_PATH);
    }

    public function getLoggerActive()
    {
        return $this->scopeConfig->getValue(self::INVENTORY_COMPENSATION_LOGGER_ACTIVE_XML_PATH);
    }

    public function getNumbersOrderClean()
    {
        return $this->scopeConfig->getValue(self::CLEAN_RESERVATION_NUMBERS_ORDERS_CLEAN);
    }

    public function getStatusesNeedClean()
    {
        return $this->scopeConfig->getValue(self::CLEAN_RESERVATION_STATUSES_TO_CLEAN);
    }
}
