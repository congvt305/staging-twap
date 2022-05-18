<?php
/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */
namespace Atome\MagentoPayment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class CheckoutConfigProvider implements ConfigProviderInterface
{
    protected $paymentGatewayConfig;

    public function __construct(\Atome\MagentoPayment\Model\Config\PaymentGatewayConfig $config)
    {
        $this->paymentGatewayConfig = $config;
    }

    /**
     * config for `window.checkoutConfig` in JS
     *
     * @return array
     */
    public function getConfig()
    {
        // will be rendered to `window.checkoutConfig.payment.atome` in js
        return [
            'payment' => [
                'atome' => [
                    'isActive' => $this->paymentGatewayConfig->isActive(),
                ],
            ],
        ];
    }
}
