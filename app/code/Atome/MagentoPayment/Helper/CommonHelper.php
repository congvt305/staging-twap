<?php
/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */
namespace Atome\MagentoPayment\Helper;

class CommonHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    const EXP_CODE_PAYMENT_PROCESSING = -1010;

    protected $logger;
    protected $paymentGatewayConfig;
    protected $moduleList;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Atome\MagentoPayment\Model\Logger\Logger $logger,
        \Atome\MagentoPayment\Model\Config\PaymentGatewayConfig $paymentGatewayConfig,
        \Magento\Framework\Module\ModuleListInterface $moduleList
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->paymentGatewayConfig = $paymentGatewayConfig;
        $this->moduleList = $moduleList;
    }

    public function debug($message, array $context = [])
    {
        if ($this->paymentGatewayConfig->isDebugEnabled()) {
            $this->logger->debug($message, $context);
        }
    }

    public function logInfo($message, array $context = [])
    {
        $this->logger->info($message, $context);
    }

    public function logError($message, array $context = [])
    {
        $this->logger->error($message, $context);
    }

    public function getModuleVersion()
    {
        $moduleInfo = $this->moduleList->getOne('Atome_MagentoPayment');
        return $moduleInfo['setup_version'];
    }

    public function getConfig($path)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
