<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-03
 * Time: 오후 5:18
 */

namespace Amore\Sap\Model\Connection;

use Braintree\Base;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Amore\Sap\Model\Source\Config;
use Amore\Sap\Logger\Logger;
use CJ\Middleware\Helper\Data as MiddlewareHelper;
use Amore\Base\Model\BaseRequest;

class Request extends BaseRequest
{
    const URL_REQUEST = 'sap/general/url';

    const ORDER_CONFIRM_PATH = 'sap/url_path/order_confirm_path';

    const ORDER_CANCEL_PATH = 'sap/url_path/order_cancel_path';

    /**
     * @var Config
     */
    private $config;
    /**
     * @var Logger
     */
    private $logger;

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
        parent::__construct($curl, $json, $middlewareHelper);
        $this->config = $config;
        $this->logger = $logger;
    }

    public function postRequest($requestData, $storeId, $type = 'confirm')
    {
        $url = $this->getUrl($storeId);
        $path = $this->getPath($storeId, $type);
        $fullUrl = $url . $path;
        if ($this->config->getLoggingCheck()) {
            $this->logger->info('LIVE MODE REQUEST');
            $this->logger->info(json_encode($requestData));
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

                $response = $this->send($fullUrl, $requestData, $storeId, 'store', $type);

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

    public function handleResponse($response, $storeId)
    {
        $resultSize = is_array($response) ? count($response) : 0 ;
        if ($resultSize > 0) {
            $success = true;
            $message = '';
            if ($this->middlewareHelper->isNewMiddlewareEnabled('store', $storeId)) {
                if ($response['success'] != true) {
                    $success = $response['success'];
                    if (empty($response['data']) || empty($response['data']['response'])) {
                        $response['data']['response'] = '';
                        if (!empty($response['data']['message'])) {
                            $message = $response['data']['message'];
                        }
                    }
                }
            } else {
                if ($response['code'] != '0000') {
                    $success = false;
                    $message = $response['message'];
                }
            }

            return [
                'success' => $success,
                'data' => $response['data']['response'],
                'message' => $message
            ];
        } else {
            return null;
        }
    }
}
