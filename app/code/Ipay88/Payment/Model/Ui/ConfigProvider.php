<?php

namespace Ipay88\Payment\Model\Ui;

class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    const CODE = 'ipay88_payment';

    /**
     * @var \Ipay88\Payment\Gateway\Config\Config
     */
    protected $ipay88PaymentGatewayConfig;

    /**
     * ConfigProvider constructor.
     *
     * @param  \Ipay88\Payment\Gateway\Config\Config  $ipay88PaymentGatewayConfig
     */
    public function __construct(
        \Ipay88\Payment\Gateway\Config\Config $ipay88PaymentGatewayConfig
    ) {
        $this->ipay88PaymentGatewayConfig = $ipay88PaymentGatewayConfig;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'showAvailablePaymentTypes'           => $this->ipay88PaymentGatewayConfig->getShowAvailablePaymentTypes(),
                    'onlineBankingMethods'                => $this->ipay88PaymentGatewayConfig->getOnlineBankingMethods(),
                    'creditCardMethods'                   => $this->ipay88PaymentGatewayConfig->getCreditCardMethods(),
                    'walletMethods'                       => $this->ipay88PaymentGatewayConfig->getWalletMethods(),
                    'buyNowPayLaterMethods'               => $this->ipay88PaymentGatewayConfig->getBuyNowPayLaterMethods(),
                    'groupPaymentMethodsByTypeOnCheckout' => $this->ipay88PaymentGatewayConfig->getGroupPaymentMethodsByTypeOnCheckout(),
                ],
            ],
        ];
    }
}