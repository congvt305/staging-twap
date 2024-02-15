<?php
declare(strict_types=1);

namespace Eguana\EInvoice\Model;

/**
 * Class EInvoiceService
 */
class EInvoiceService
{
    const KEY_TIMESTAMP = 'TimeStamp';
    const KEY_MERCHANT_ID = 'MerchantID';
    const KEY_RELATE_NUMBER = 'RelateNumber';
    const KEY_INVOICE_DATE = 'InvoiceDate';
    const KEY_INVOICE_NUMBER = 'InvoiceNumber';
    const KEY_RANDOM_NUMBER = 'RandomNumber';
    const KEY_RTN_MESSAGE = 'RtnMsg';
    const KEY_RTN_CODE = 'RtnCode';
    const KEY_CHECK_MAC_VALUE = 'CheckMacValue';

    /**
     * @var array
     */
    protected $mapper = [
        self::KEY_INVOICE_DATE => 'IIS_Create_Date',
        self::KEY_INVOICE_NUMBER => 'IIS_Number',
        self::KEY_RANDOM_NUMBER => 'IIS_Random_Number',
        self::KEY_RTN_MESSAGE => 'RtnMsg',
        self::KEY_RTN_CODE => 'RtnCode',
        self::KEY_CHECK_MAC_VALUE => 'CheckMacValue',
        self::KEY_RELATE_NUMBER => 'IIS_Relate_Number'
    ];

    /**
     * @var \Ecpay\Ecpaypayment\Helper\Library\ECPayInvoiceCheckMacValue
     */
    protected $checkMacValue;

    /**
     * @var \Eguana\EInvoice\Model\Config
     */
    protected $helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Eguana\EInvoice\Model\Config $helper
     * @param \Ecpay\Ecpaypayment\Helper\Library\ECPayInvoiceCheckMacValue $checkMacValue
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Eguana\EInvoice\Model\Config $helper,
        \Ecpay\Ecpaypayment\Helper\Library\ECPayInvoiceCheckMacValue $checkMacValue,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->checkMacValue = $checkMacValue;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Check if einvoice has been created by ecpay for this order
     *
     * @param int $orderId
     * @return bool
     * @throws \Exception
     */
    public function eInvoiceIssued(int $orderId): bool
    {
        return !empty($this->fetchEInvoiceDetail($orderId));
    }

    /**
     * Call ECPAY API to get invoice information
     * if invoice is not exist, it returns an empty array
     *
     * @param int $orderId
     * @return array
     */
    public function fetchEInvoiceDetail(int $orderId): array
    {
        $order = $this->orderRepository->get($orderId);
        if (!empty($output = $this->getEcpayEInvoiceData($order))) {
            $invoiceData = [];
            foreach ($this->mapper as $key => $value) {
                $invoiceData[$key] = $output[$value];
            }
            return $invoiceData;
        } else {
            return [];
        }
    }
    /**
     * Call API to query einvoice information
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    protected function getEcpayEInvoiceData(\Magento\Sales\Model\Order $order): array
    {
        $orderId = (int) $order->getStoreId();
        $serviceUrl = $this->helper->getQueryEInvoiceApiUrl($orderId);
        $request = $this->buildRequestData($order);
        try {
            $ch = curl_init();
            if (false === $ch) {
                throw new \Exception('curl failed to initialize');
            }
            curl_setopt($ch, CURLOPT_URL, $serviceUrl);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            $response = curl_exec($ch);
            $this->addLog($request, $response, [
                'endpoint' => $serviceUrl,
                'order_id' => $order->getIncrementId()
            ]);
            parse_str($response, $output);
            if (!(isset($output[self::KEY_RTN_CODE]) && $output[self::KEY_RTN_CODE] == 1)) {
                return [];
            } else {
                return $output;
            }
        } catch (\Exception $exception) {
            return [];
        }
    }

    /**
     * Build request data
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    protected function buildRequestData(\Magento\Sales\Model\Order $order): string
    {
        $storeId = (int)$order->getStoreId();
        $merchantId = $this->helper->getMerchantId($storeId);
        $relateNumber = $order->getIncrementId();
        $checkMacValue = $this->checkMacValue->generate(
            [
                self::KEY_TIMESTAMP => time(),
                self::KEY_MERCHANT_ID => $merchantId,
                self::KEY_RELATE_NUMBER => $relateNumber
            ],
            $this->helper->getHashKey($storeId),
            $this->helper->getHashIv($storeId)
        );
        $requestData = [
            self::KEY_TIMESTAMP => time(),
            self::KEY_MERCHANT_ID => $merchantId,
            self::KEY_RELATE_NUMBER => $relateNumber,
            self::KEY_CHECK_MAC_VALUE => $checkMacValue
        ];

        return http_build_query($requestData);
    }

    /**
     * Write log
     *
     * @param string $request
     * @param string $response
     * @param array $context
     */
    private function addLog(string $request, string $response, array $context = []): void
    {
        $this->logger->log('info', 'QUERY EINVOICE INFO', $context);
        $this->logger->log('info', 'REQUEST DATA', $context);
        $this->logger->log('info', $request, $context);
        $this->logger->log('info', 'RESPONSE DATA', $context);
        $this->logger->log('info', $response, $context);
    }
}
