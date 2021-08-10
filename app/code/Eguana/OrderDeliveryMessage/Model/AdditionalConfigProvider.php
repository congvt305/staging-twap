<?php

namespace Eguana\OrderDeliveryMessage\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class AdditionalConfigProvider
 */
class AdditionalConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
      ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig =  $scopeConfig;
    }

    /**
     * @return string
     */
    private function getValidateDeliveryMessLengthEnabled() {
        return $this->scopeConfig->getValue('deliverymessage/general/is_validate_enabled', 'store');
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $output['validate_delivery_message_enabled'] = $this->getValidateDeliveryMessLengthEnabled();
        return $output;
    }
}
