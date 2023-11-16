<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오전 11:38
 */

namespace Amore\PointsIntegration\Model\Connection;

use Amore\PointsIntegration\Exception\PosPointsException;
use Amore\PointsIntegration\Model\Source\Config;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;
use Amore\PointsIntegration\Logger\Logger;
use CJ\Middleware\Helper\Data as MiddlewareHelper;
use Amore\Base\Model\BaseRequest;

class Request extends BaseRequest
{
    /**
     * @var Config
     */
    private $config;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * Request constructor.
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

    /**
     * @param $requestData
     * @param $websiteId
     * @param $type
     * @return array|bool|float|int|mixed|string|null
     */
    public function sendRequest($requestData, $websiteId, $type)
    {
        $isNewMiddlewareEnable = $this->middlewareHelper->isNewMiddlewareEnabled('website', $websiteId);
        $url = $this->getUrl($type, $websiteId);
        if (!$url && !$isNewMiddlewareEnable) {
            $this->logger->info("URL IS EMPTY");
            return [];
        }

        $logEnabled = $this->config->getLoggerActiveCheck($websiteId);
        if ($logEnabled) {
            $this->logger->info('POS Request: ' . $this->json->serialize($requestData));
        }
        try {
            $this->curl->addHeader('Content-Type', 'application/json');
            if ($this->config->getSSLVerification($websiteId)) {
                $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, false);
                $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
            }

            $response = $this->send($url, $requestData, $websiteId, 'website', $type);

            if ($logEnabled) {
                $this->logger->info('POS Response: ' . $this->json->serialize($response));
            }

            return $this->json->unserialize($response);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            return [];
        }
    }

    public function getUrl($type, $websiteId)
    {
        $path = '';
        switch ($type) {
            case 'memberSearch':
                $path = $this->config->getMemberSearchURL($websiteId);
                break;
            case 'redeemSearch':
                $path = $this->config->getRedeemSearchURL($websiteId);
                break;
            case 'pointSearch':
                $path = $this->config->getPointSearchURL($websiteId);
                break;
            case 'customerOrder':
                $path = $this->config->getCustomerOrderURL($websiteId);
                break;
        }
        return $path;
    }

    public function responseCheck($response, $websiteId)
    {
        $isNewMiddlewareEnable = $this->middlewareHelper->isNewMiddlewareEnabled('website', $websiteId);
        if ($isNewMiddlewareEnable) {
            if ((isset($response['success']) && $response['success']) &&
                ((isset($response['data']['statusCode']) && $response['data']['statusCode'] == '200') ||
                    (isset($response['data']['statusCode']) && $response['data']['statusCode'] == '12' &&
                        isset($response['data']['statusMessage']) && $response['data']['statusMessage'] == 'The OrderId is duplicated.'))
            ){
                return 1;
            } else {
                return 0;
            }
        } else {
            if (isset($response['data']['statusCode']) && $response['data']['statusCode'] == '200') {
                return 1;
            } else {
                return 0;
            }
        }
    }
}
