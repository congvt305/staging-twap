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
use CJ\Middleware\Helper\Data as MiddlewareHelper;

class Request
{
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
     * @var MiddlewareHelper
     */
    protected $middlewareHelper;

    /**
     * @param Curl $curl
     * @param Json $json
     * @param Config $config
     * @param Logger $logger
     * @param MiddlewareHelper $middlewareHelper
     */
    public function __construct(
        Curl $curl,
        Json $json,
        Config $config,
        Logger $logger,
        MiddlewareHelper $middlewareHelper
    ) {
        $this->curl = $curl;
        $this->json = $json;
        $this->config = $config;
        $this->logger = $logger;
        $this->middlewareHelper = $middlewareHelper;
    }

    public function postRequest($requestData, $storeId, $type = 'confirm')
    {
        $url = $this->getUrl($storeId);
        $path = $this->getPath($storeId, $type);
        $fullUrl = $url . $path;
        $isNewMiddlewareEnable = $this->middlewareHelper->isNewMiddlewareEnabled('store', $storeId);
        if ($isNewMiddlewareEnable) {
            $fullUrl = $this->middlewareHelper->getNewMiddlewareURL('store', $storeId);
            $requestData = $this->json->unserialize($requestData);
            $requestData['APP_ID'] = $this->getInterfaceID($storeId, $type);
            $requestData['API_USER_ID'] = $this->middlewareHelper->getMiddlewareUsername('store', $storeId);
            $requestData['AUTH_KEY'] = $this->middlewareHelper->getMiddlewareAuthKey('store', $storeId);
            $requestData = $this->json->serialize($requestData);
        }
        if ($this->config->getLoggingCheck()) {
            $this->logger->info('LIVE MODE REQUEST');
            $this->logger->info($requestData);
            $this->logger->info("FUlL URL");
            $this->logger->info($fullUrl);
        }

        if (empty($fullUrl)) {
            throw new LocalizedException(__("Url or Path is empty. Please check configuration and try again."));
        } else {
            try {
                $this->curl->addHeader('Content-Type', 'application/json');

                if ($this->config->getSslVerification('default', 0)) {
                    if ($this->config->getLoggingCheck()) {
                        $this->logger->info('SSL VERIFICATION DISABLED');
                    }
                    $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, false);
                    $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
                }

                $this->curl->post($fullUrl, $requestData);

                $response = $this->curl->getBody();

                if ($this->config->getLoggingCheck()) {
                    $this->logger->info('LIVE RESPONSE');
                    $this->logger->info($response);
                }

                $result = $this->json->unserialize($response);

                return $result;
            } catch (\Exception $exception) {
                return $exception->getMessage();
            }
        }
    }

    public function getUrl($storeId)
    {
        $url = '';

        $activeCheck = $this->config->getActiveCheck('store', $storeId);

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

    public function getInterfaceID($storeId, $type)
    {
        switch ($type) {
            case 'confirm':
                $path = $this->middlewareHelper->getOrderConfirmInterfaceId('store', $storeId);
                break;
            case 'cancel':
                $path = $this->middlewareHelper->getOrderCancelInterfaceId('store', $storeId);
                break;
            default:
                $path = $this->middlewareHelper->getOrderConfirmInterfaceId('store', $storeId);
        }
        return $path;
    }
}
