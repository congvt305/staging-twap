<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 8/7/20
 * Time: 12:39 PM
 */
namespace Eguana\EventManager\Model\EventManagerConfiguration;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * This class is used to get configuration values from Admin Configuration
 *
 * Class EventManagerConfiguration
 */
class EventManagerConfiguration
{
    /**
     * Constants
     */
    const XML_GENERAL_PATH = 'eventmanager/general/';

    const XML_PATH_LOAD_MORE_FIELD = 'load_more_id';

    const XML_PATH_SORT_ORDER_FIELD = 'sort_order_id';

    /**
     * @var ScopeInterface
     */
    private $scopeConfig;

    /**
     * Constructor
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get Config Value
     * This Method is used to get configuration value on the bases of field parameter
     * @param $field
     * @return int
     */
    public function getConfigValue($field)
    {
        return $this->scopeConfig->getValue(
            self::XML_GENERAL_PATH . $field,
            ScopeInterface::SCOPE_STORE
        );
    }
}
