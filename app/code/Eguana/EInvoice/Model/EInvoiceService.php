<?php
declare(strict_types=1);

namespace Eguana\EInvoice\Model;

/**
 * Class EInvoiceService
 */
class EInvoiceService
{
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
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    const ECPAY_QUERY_TEST_FLAG = 'ecpay_query_test_flag';

    const ECPAY_QUERY_STAGE_URL = 'ecpay_query_stage_url';

    const ECPAY_QUERY_PRODUCTION_URL = 'ecpay_query_production_url';

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Ecpay\Ecpaypayment\Helper\Library\ECPayInvoiceCheckMacValue $ECPayInvoiceCheckMacValue
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecpay\Ecpaypayment\Helper\Library\ECPayInvoiceCheckMacValue $ECPayInvoiceCheckMacValue,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
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
        $order = $this->orderRepository->get($orderId);
        $relateNumber = $order->getIncrementId();
        $storeId = $order->getStoreId();
        $merchantId = $this->getEcpayConfigFromStore('merchant_id', $storeId);
        $checkMacValue = $this->ECPayInvoiceCheckMacValue->generate(
            [
                'TimeStamp' => time(),
                'MerchantID' => $merchantId,
                'RelateNumber' => $relateNumber
            ],
            $this->getEcpayConfigFromStore('invoice/ecpay_invoice_hash_key', $storeId),
            $this->getEcpayConfigFromStore('invoice/ecpay_invoice_hash_iv', $storeId)
        );
        $requestData = [
            'TimeStamp' => time(),
            'MerchantID' => $merchantId,
            'RelateNumber' => $relateNumber,
            'CheckMacValue' => $checkMacValue
        ];
        $requestData = http_build_query($requestData);
        $ServiceURL = $this->getQueryEInvoiceApiUrl($storeId);
        $ch = curl_init();
        if (false === $ch) {
            throw new \Exception('curl failed to initialize');
        }
        curl_setopt($ch, CURLOPT_URL, $ServiceURL);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData);
        $result = curl_exec($ch);
        $context = ['order_increment_id' => $relateNumber];
        $this->logger->log('info', 'QUERY EINVOICE INFO', $context);
        $this->logger->log('info', 'API URL: ' . $ServiceURL, $context);
        $this->logger->log('info', 'REQUEST DATA: ' . json_encode($requestData), $context);
        $this->logger->log('info', 'RESPONSE DATA: ' . $result, $context);
        parse_str($result, $output);
        return isset($output['RtnCode']) && $output['RtnCode'] == 1;
    }

    /**
     * @param int $storeId
     * @return string
     */
    protected function getQueryEInvoiceApiUrl($storeId)
    {
        $testFlag = $this->scopeConfig->getValue(self::ECPAY_QUERY_TEST_FLAG, 'store', $storeId);
        if ($testFlag) {
            return $this->scopeConfig->getValue(self::ECPAY_QUERY_STAGE_URL, 'store', $storeId) . 'Issue';
        } else {
            return $this->scopeConfig->getValue(self::ECPAY_QUERY_PRODUCTION_URL, 'store', $storeId) . 'Issue';
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
