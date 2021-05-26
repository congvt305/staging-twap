<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 24/5/21
 * Time: 8:08 PM
 */
namespace Eguana\RedInvoice\Model\RedInvoiceConfig;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * This class is used for the red invoice admin panle configurations
 * Class RedInvoiceConfig
 */
class RedInvoiceConfig
{
    /**
     * Constants
     */
    const XML_PATH_MODULE_ENABLED = 'redinvoice/general/active';
    const XML_PATH_DEBUG_ENABLED = 'redinvoice/general/debug';

    /**
     * @var ScopeInterface
     */
    private $scopeConfig;

    /**
     * RedInvoiceConfig Constructor
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get Module Enable Value
     * This method is used to check if the option is enable or disable
     * @param null $websiteId
     * @return mixed
     */
    public function getEnableValue($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MODULE_ENABLED,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Get Debug Enable Value
     * This method is used to check if the debug option is enable or disable
     * @param null $websiteId
     * @return mixed
     */
    public function getDebugEnableValue($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEBUG_ENABLED,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }
}
