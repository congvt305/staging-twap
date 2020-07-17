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
