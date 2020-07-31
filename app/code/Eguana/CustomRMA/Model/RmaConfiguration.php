<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: mobeen
 * Date: 17/7/20
 * Time: 2:21 PM
 */
namespace Eguana\CustomRMA\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * This class is used to get configuration values from Admin Configuration
 *
 * Class RmaConfiguration
 */
class RmaConfiguration
{
    /**
     * Constants
     */
    const XML_GENERAL_PATH_ACTIVE = 'eguanacustomrma/general/active';
    const XML_GENERAL_PATH_RESOLUTION = 'eguanacustomrma/general/resolution';
    const XML_GENERAL_PATH_CONDITION = 'eguanacustomrma/general/condition';
    const XML_GENERAL_PATH_REASON = 'eguanacustomrma/general/reason';
    const XML_GENERAL_PATH_REASON_OTHER = 'eguanacustomrma/general/reason_other';

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
     * This Method is used to get configuration value
     * @return int
     */
    public function isRmaActive()
    {
        return $this->scopeConfig->getValue(
            self::XML_GENERAL_PATH_ACTIVE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config Value
     * This Method is used to get rma resolution value
     * @return int
     */
    public function getRmaResolution()
    {
        return $this->scopeConfig->getValue(
            self::XML_GENERAL_PATH_RESOLUTION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config Value
     * This Method is used to get rma condition value
     * @return int
     */
    public function getRmaCondition()
    {
        return $this->scopeConfig->getValue(
            self::XML_GENERAL_PATH_CONDITION,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config Value
     * This Method is used to get rma reason value
     * @return int
     */
    public function getRmaReason()
    {
        return $this->scopeConfig->getValue(
            self::XML_GENERAL_PATH_REASON,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Config Value
     * This Method is used to get rma reason other comment value
     * @return int
     */
    public function getRmaReasonOther()
    {
        return $this->scopeConfig->getValue(
            self::XML_GENERAL_PATH_REASON_OTHER,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get relevant template file for RMA form
     * @return string
     */
    public function getTemplate()
    {
        if ($this->isRmaActive()) {
            $template =  'Eguana_CustomRMA::return/create.phtml';
        } else {
            $template = 'Magento_Rma::return/create.phtml';
        }

        return $template;
    }
}
