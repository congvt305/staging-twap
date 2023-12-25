<?php
namespace Sapt\Ajaxcart\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var array
     */
    protected $configModule;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Module\Manager $moduleManager
    )
    {
        $this->_moduleManager = $moduleManager;
        parent::__construct($context);
        $this->configModule = $this->getConfig(strtolower($this->_getModuleName()));
    }

    public function getConfig($cfg='')
    {
        if($cfg) return $this->scopeConfig->getValue( $cfg, \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
        return $this->scopeConfig;
    }

    public function getConfigModule($cfg='', $value=null)
    {
        $values = $this->configModule;
        if( !$cfg ) return $values;
        $config  = explode('/', $cfg);
        $end     = count($config) - 1;
        foreach ($config as $key => $vl) {
            if( isset($values[$vl]) ){
                if( $key == $end ) {
                    $value = $values[$vl];
                }else {
                    $values = $values[$vl];
                }
            }

        }
        return $value;
    }

    /**
     * Returns if module exists or not
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isModuleEnabled($moduleName)
    {
      return $this->_moduleManager->isEnabled($moduleName);
    }

    /**
     * Is ajax cart enabled.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'sapt_ajaxcart/general/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is ajax cart enabled in product view.
     *
     * @return bool
     */
    public function isEnabledProductView()
    {
        return $this->scopeConfig->isSetFlag(
            'sapt_ajaxcart/general/enabled_product_view',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
