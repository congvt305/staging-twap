<?php
declare(strict_types=1);

namespace Eguana\EInvoice\Model;

/**
 * Class EInvoiceService
 */
class EInvoiceService
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Ecpay\Ecpaypayment\Helper\Library\ECPayInvoiceCheckMacValue
     */
    protected $ECPayInvoiceCheckMacValue;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ecpay\Ecpaypayment\Helper\Library\ECPayInvoiceCheckMacValue $ECPayInvoiceCheckMacValue
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecpay\Ecpaypayment\Helper\Library\ECPayInvoiceCheckMacValue $ECPayInvoiceCheckMacValue,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->orderFactory = $orderFactory;
        $this->scopeConfig = $scopeConfig;
        $this->ECPayInvoiceCheckMacValue = $ECPayInvoiceCheckMacValue;
        $this->logger = $logger;
    }

    /**
     * Check if einvoice has been created by ecpay for this order
     *
     * @param $orderId
     * @return bool
     * @throws \Exception
     */
    public function eInvoiceIssued($orderId): bool
    {
        $order = $this->orderFactory->create()->load($orderId);
        $relateNumber = $order->getIncrementId();
        $storeId = $order->getStoreId();
        $timestamp = time();
        $merchantId = $this->getEcpayConfigFromStore('merchant_id', $storeId);
        $ch = curl_init();
        if (false === $ch) {
            throw new \Exception('curl failed to initialize');
        }
        $checkMacValue = $this->ECPayInvoiceCheckMacValue->generate(
            [
                'TimeStamp' => time(),
                'MerchantID' => $merchantId,
                'RelateNumber' => $relateNumber
            ],
            $this->getEcpayConfigFromStore('invoice/ecpay_invoice_hash_key', $storeId),
            $this->getEcpayConfigFromStore('invoice/ecpay_invoice_hash_iv', $storeId)
        );
        $sSend_Info = [
            'TimeStamp' => time(),
            'MerchantID' => $merchantId,
            'RelateNumber' => $relateNumber,
            'CheckMacValue' => $checkMacValue
        ];
        $sSend_Info = http_build_query($sSend_Info);
        $ServiceURL = $this->getInvoiceApiUrl($storeId) . 'Issue';
        curl_setopt($ch, CURLOPT_URL, $ServiceURL);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sSend_Info);
        $this->logger->log('info', 'QUERY EINVOICE INFO', ['order_increment_id' => $relateNumber]);
        $this->logger->log('info', 'API URL: ' . $ServiceURL);
        $this->logger->log('info', 'REQUEST DATA: ' . json_encode($sSend_Info),
            ['order_increment_id' => $relateNumber]);
        $rs = curl_exec($ch);
        $this->logger->log('info', 'RESPONSE DATA: ' . $rs, ['order_increment_id' => $relateNumber]);
        parse_str($rs, $output);
        return isset($output['RtnCode']) && $output['RtnCode'] == 1;
    }

    /**
     * @param int $storeId
     * @return string
     */
    protected function getInvoiceApiUrl($storeId)
    {
        if ($this->scopeConfig->getValue('eguana_einvoice/ecpay_einvoice_issue/ecpay_query_test_flag', 'store', $storeId)) {
            return $this->scopeConfig->getValue('eguana_einvoice/ecpay_einvoice_issue/ecpay_query_stage_url', 'store', $storeId);
        } else {
            return $this->scopeConfig->getValue('eguana_einvoice/ecpay_einvoice_issue/ecpay_query_production_url', 'store', $storeId);
        }
    }

    /**
     * @param string $id
     * @param int $storeId
     * @return string
     */
    protected function getEcpayConfigFromStore($id, $storeId)
    {
        $prefix = "payment/ecpay_ecpaypayment/ecpay_";
        $path = $prefix . $id;
        return $this->scopeConfig->getValue($path, 'store', $storeId);
    }
}
