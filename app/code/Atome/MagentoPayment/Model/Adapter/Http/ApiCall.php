<?php
/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */
namespace Atome\MagentoPayment\Model\Adapter\Http;

use Atome\MagentoPayment\Helper\CommonHelper;
use Atome\MagentoPayment\Model\Config\PaymentGatewayConfig;
use Magento\Framework\HTTP\ZendClientFactory;

class ApiCall
{
    protected $commonHelper;
    protected $httpClientFactory;
    protected $paymentGatewayConfig;
    protected $magentoProductMetadata;
    protected $storageManager;
    protected $logger;

    public function __construct(
        ZendClientFactory $httpClientFactory,
        PaymentGatewayConfig $paymentGatewayConfig,
        CommonHelper $commonHelper,
        \Magento\Framework\App\ProductMetadataInterface $magentoProductMetadata,
        \Magento\Store\Model\StoreManagerInterface $storageManager
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->paymentGatewayConfig = $paymentGatewayConfig;
        $this->commonHelper = $commonHelper;
        $this->magentoProductMetadata = $magentoProductMetadata;
        $this->storageManager = $storageManager;
    }

    /**
     * @param string $url
     * @param bool $body
     * @param string $method
     * @return \Zend_Http_Response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function send($url, $body = null, $method = 'GET')
    {
        $website = $this->storageManager->getStore()->getBaseUrl();
        $phpVersion = phpversion();
        $phpOs = PHP_OS;
        $ua1 = "AtomeMagento2/{$this->commonHelper->getModuleVersion()} ($phpOs; PHP/$phpVersion)";
        $ua2 = "{$this->magentoProductMetadata->getName()}/{$this->magentoProductMetadata->getVersion()} ({$this->magentoProductMetadata->getEdition()})";
        $ua3 = "Merchant=" . rawurlencode($this->paymentGatewayConfig->getMerchantApiKey()) . '&Website=' . rawurlencode($website);
        $userAgent = "$ua1 $ua2 $ua3";
        $this->commonHelper->logInfo('api call request: ' . json_encode([
            'type' => 'Request',
            'method' => $method,
            'url' => $url,
            'userAgent' => $userAgent,
            'body' => $this->maskSensitiveData($body),
        ], JSON_UNESCAPED_SLASHES));

        try {
            $client = $this->httpClientFactory->create();
            $client->setUri($url);
            if ($body) {
                $client->setRawData(json_encode($body), 'application/json');
            }
            $client->setAuth(trim($this->paymentGatewayConfig->getMerchantApiKey()), trim($this->paymentGatewayConfig->getMerchantApiSecret()));
            $client->setConfig([
                'timeout' => 80,
                'maxredirects' => 0,
                'useragent' => $userAgent,
            ]);
            $response = $client->request($method);
            $this->commonHelper->logInfo('api call response: ' . json_encode([
                'type' => 'Response',
                'method' => $method,
                'url' => $url,
                'status' => $response->getStatus(),
                'body' => $this->maskSensitiveData(json_decode($response->getBody()))
            ]));
        } catch (\Exception $e) {
            $this->commonHelper->logError('api call exception: ' . $e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(__('Gateway error: %1', $e->getMessage()));
        }
        return $response;
    }

    private function maskSensitiveData($data)
    {
        $sensitiveFields = ["shippingAddress", "billingAddress", "customerInfo"];
        if (!empty($data)) {
            foreach ($data as $key => $val) {
                if (is_array($val)) {
                    $isSensitiveField = in_array($key, $sensitiveFields);
                    foreach ($val as $k => $v) {
                        if (substr($k, 0, 1) === '_') {
                            unset($data[$key][$k]);
                        } elseif (is_array($v)) {
                            $data[$key][$k] = '[array (' . count($v) . ')]';
                        } elseif ($isSensitiveField) {
                            $data[$key][$k] = (strlen("$v") <= 2) ? $v : (substr($v, 0, 2) . str_repeat("*", strlen($v) - 2));
                        }
                    }
                }
                if (substr($key, 0, 1) === '_') {
                    unset($data[$key]);
                }
            }
        }
        return $data;
    }
}
