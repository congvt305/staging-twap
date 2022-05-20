<?php

namespace Ipay88\Payment\Gateway\Request;

class InitializationRequest implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    /**
     * @var \Ipay88\Payment\Gateway\Config\Config
     */
    protected $ipay88PaymentGatewayConfig;

    /**
     * AuthorizationRequest constructor.
     *
     * @param  \Ipay88\Payment\Gateway\Config\Config  $config
     */
    public function __construct(
        \Ipay88\Payment\Gateway\Config\Config $config
    ) {
        $this->ipay88PaymentGatewayConfig = $config;
    }

    /**
     * Builds ENV request
     *
     * @param  array  $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        /**
         * @var \Magento\Framework\DataObject $stateObject
         */
        $stateObject = $buildSubject['stateObject'];
        $stateObject->setData('state', \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setData('status', \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setData('is_notified', false);

        return [];
    }
}