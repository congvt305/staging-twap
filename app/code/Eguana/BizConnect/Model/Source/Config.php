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

    public function getActive()
    {
        return $this->scopeConfig->getValue(self::BIZCONNECT_LOG_DELETE_CRON_ACTIVE);
    }

    public function getNumbersToDelete()
    {
        return $this->scopeConfig->getValue(self::BIZCONNECT_LOG_DELETE_CRON_NUMBERS_TO_DELETE);
    }
}
