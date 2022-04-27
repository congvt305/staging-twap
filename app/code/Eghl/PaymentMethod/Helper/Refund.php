<?php

namespace Eghl\PaymentMethod\Helper;

use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Refund extends AbstractHelper
{
    const XML_PATH_EGHL = 'payment/eghlpayment/';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Refund constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        LoggerInterface $logger
    ) {
        $this->storeManager    = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->logger          = $logger;
        parent::__construct($context);
    }

    /**
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $code
     * @param null $storeId
     * @return mixed
     */
    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_EGHL . $code, $storeId);
    }

    /**
     * @param $refundRequest
     * @param null $storeId
     * @return string
     */
    public function calculateHashValue($refundRequest, $storeId = null)
    {
        $clearString	  =	$this->getGeneralConfig('hashpass', $storeId);
        $hashStrKeysOrder = [
            'ServiceID',
            'PaymentID',
            'Amount',
            'CurrencyCode'
        ];

        foreach ($hashStrKeysOrder as $ind) {
            $clearString .= $refundRequest[$ind];
        }

        return hash('sha256', $clearString);
    }

    /**
     * @param $message
     */
    public function log($message)
    {
        $this->logger->info($message);
    }
}
