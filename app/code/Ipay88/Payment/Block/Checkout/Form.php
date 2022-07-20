<?php

namespace Ipay88\Payment\Block\Checkout;

/**
 * Class Form
 * @package Ipay88\Payment\Block\Checkout
 *
 * @method string getMerchantCode()
 * @method string getPaymentId()
 * @method string getRefNo()
 * @method string getAmount()
 * @method string getCurrency()
 * @method string getProdDesc()
 * @method string getUserName()
 * @method string getUserEmail()
 * @method string getUserContact()
 * @method string getRemark()
 * @method string getLang()
 * @method string getSignatureType()
 * @method string getSignature()
 * @method string getResponseUrl()
 * @method string getBackendUrl()
 * @method string getAppdeeplink()
 * @method string getXfield1()
 */
class Form extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $magentoCheckoutSession;

    /**
     * @var \Ipay88\Payment\Helper\Data
     */
    protected $ipay88PaymentDataHelper;

    /**
     * @var \Ipay88\Payment\Logger\Logger
     */
    protected $ipay88PaymentLogger;

    /**
     * Form constructor.
     *
     * @param  \Magento\Framework\View\Element\Template\Context  $context
     * @param  \Magento\Checkout\Model\Session  $magentoCheckoutSession
     * @param  \Ipay88\Payment\Helper\Data  $ipay88PaymentDataHelper
     * @param  \Ipay88\Payment\Logger\Logger  $ipay88PaymentLogger
     * @param  array  $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $magentoCheckoutSession,
        \Ipay88\Payment\Helper\Data $ipay88PaymentDataHelper,
        \Ipay88\Payment\Logger\Logger $ipay88PaymentLogger,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->magentoCheckoutSession  = $magentoCheckoutSession;
        $this->ipay88PaymentDataHelper = $ipay88PaymentDataHelper;
        $this->ipay88PaymentLogger     = $ipay88PaymentLogger;
    }

    /**
     * Set order
     *
     * @param  \Magento\Sales\Model\Order  $order
     */
    public function setOrder(
        \Magento\Sales\Model\Order $order
    ) {
        $this->setData('order', $order);

        $requestData = $this->ipay88PaymentDataHelper->generateRequestData($order);

        $this->setData($requestData);

        $this->ipay88PaymentLogger->info('[form] payment request', $requestData);
    }
}