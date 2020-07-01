<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-03
 * Time: 오후 5:18
 */

namespace Amore\Sap\Model\Connection;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Amore\Sap\Model\Source\Config;
use Amore\Sap\Logger\Logger;

class Request
{
    const URL_ACTIVE = 'sap/general/active';

    const URL_REQUEST = 'sap/general/url';

    const ORDER_CONFIRM_PATH = 'sap/url_path/order_confirm_path';

    const ORDER_CANCEL_PATH = 'sap/url_path/order_cancel_path';

    /**
     * @var Curl
     */
    private $curl;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var Logger
     */
    private $logger;


    /**
     * Constructor.
     *
     * @param Curl $curl
     * @param Json $json
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        Curl $curl,
        Json $json,
        Config $config,
        Logger $logger
    ) {
        $this->curl = $curl;
        $this->json = $json;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function postRequest($requestData, $storeId, $type = 'confirm')
    {
        if ($this->config->checkTestMode()) {
            $url = $this->config->getDefaultValue("sap/general/url");
            if ($type == 'confirm') {
                $path = $this->config->getDefaultValue("sap/url_path/order_confirm_path");
            } else {
                $path = $this->config->getDefaultValue("sap/url_path/order_cancel_path");
            }
            $fullUrl = $url . $path;

            if ($this->config->getLoggingCheck()) {
                $this->logger->info("TEST MODE REQUEST");
                $this->logger->info($requestData);
                $this->logger->info("FUlL URL");
                $this->logger->info($fullUrl);
            }

            if (empty($url) || empty($path)) {
                throw new LocalizedException(__("Url or Path is empty. Please check configuration and try again."));
            } else {
                $this->curl->addHeader('Content-Type', 'application/json');
                $this->curl->post($fullUrl, $requestData);

                $response = $this->curl->getBody();
                $serializedResult = $this->json->unserialize($response);

                if ($this->config->getLoggingCheck()) {
                    $this->logger->info("TEST RESPONSE");
                    $this->logger->info($serializedResult);
                }
                return $serializedResult;
            }
        } else {
            $url = $this->getUrl($storeId);
            $path = $this->getPath($storeId, $type);
            $fullUrl = $url . $path;

            if ($this->config->getLoggingCheck()) {
                $this->logger->info('LIVE MODE REQUEST');
                $this->logger->info($requestData);
                $this->logger->info("FUlL URL");
                $this->logger->info($fullUrl);
            }


            if (empty($url) || empty($path)) {
                throw new LocalizedException(__("Url or Path is empty. Please check configuration and try again."));
            } else {
                $this->curl->addHeader('Content-Type', 'application/json');
                $this->curl->post($fullUrl, $requestData);

                $response = $this->curl->getBody();

                $result = $this->json->unserialize($response);

                if ($this->config->getLoggingCheck()) {
                    $this->logger->info('LIVE RESPONSE');
                    $this->logger->info($result);
                }

                if ($result['code'] == '0000') {
                    return $result;
                } else {
                    return $result;
                }
            }
        }
    }

    public function getUrl($storeId)
    {
        $url = '';

        $activeCheck = $this->config->getValue(self::URL_ACTIVE, 'store', $storeId);

        if ($activeCheck) {
            $url = $this->config->getValue(self::URL_REQUEST, 'store', $storeId);
        }

        return $url;
    }

    public function getPath($storeId, $type)
    {
        switch ($type) {
            case 'confirm':
                $path = $this->config->getValue(self::ORDER_CONFIRM_PATH, 'store', $storeId);
                break;
            case 'cancel':
                $path = $this->config->getValue(self::ORDER_CANCEL_PATH, 'store', $storeId);
                break;
            default:
                $path = $this->config->getValue(self::ORDER_CONFIRM_PATH, 'store', $storeId);
        }
        return $path;
    }
}
