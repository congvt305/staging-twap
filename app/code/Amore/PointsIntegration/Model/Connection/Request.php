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

class Request
{
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
        $this->curl = $curl;
        $this->json = $json;
        $this->config = $config;
        $this->logger = $logger;
        $this->middlewareHelper = $middlewareHelper;
    }

    /**
     * @param $requestData
     * @param $websiteId
     * @param $type
     * @return array|bool|float|int|mixed|string|null
     */
    public function sendRequest($requestData, $websiteId, $type)
    {
        $url = $this->getUrl($type, $websiteId);
        if (!$url) {
            $this->logger->info("URL IS EMPTY");
            return [];
        }

        $isNewMiddlewareEnable = $this->middlewareHelper->isNewMiddlewareEnabled('website', $websiteId);
        if ($isNewMiddlewareEnable) {
            $url = $this->middlewareHelper->getNewMiddlewareURL('website', $websiteId);
            $requestData['APP_ID'] = $this->getInterfaceID($websiteId, $type);
            $requestData['API_USER_ID'] = $this->middlewareHelper->getMiddlewareUsername('website', $websiteId);
            $requestData['AUTH_KEY'] = $this->middlewareHelper->getMiddlewareAuthKey('website', $websiteId);
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

            $this->curl->post($url, $this->json->serialize($requestData));

            $response = $this->curl->getBody();

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
            // get all customer grades
            case 'customerGrade':
                $path = $this->config->getAllCustomerGradeURL($websiteId);
                break;
        }
        return $path;
    }

    /**
     * Get Interface Id for replacing path corresponding
     * @param $websiteId
     * @param $type
     * @return mixed
     */
    public function getInterfaceID($websiteId, $type)
    {
        $path = '';
        switch ($type) {
            case 'memberSearch':
                $path = $this->middlewareHelper->getMemberSearchInterfaceId('website', $websiteId);
                break;
            case 'redeemSearch':
                $path = $this->middlewareHelper->getRedeemSearchInterfaceId('website', $websiteId);
                break;
            case 'pointSearch':
                $path = $this->middlewareHelper->getPointSearchInterfaceId('website', $websiteId);
                break;
            case 'customerOrder':
                $path = $this->middlewareHelper->getCustomerSearchInterfaceId('website', $websiteId);
                break;
        }
        return $path;
    }

    /**
     * Get Interface Id for replacing path corresponding
     * @param $websiteId
     * @param $type
     * @return mixed
     */
    public function getInterfaceID($websiteId, $type)
    {
        $path = '';
        switch ($type) {
            case 'memberSearch':
                $path = $this->middlewareHelper->getMemberSearchInterfaceId('website', $websiteId);
                break;
            case 'redeemSearch':
                $path = $this->middlewareHelper->getRedeemSearchInterfaceId('website', $websiteId);
                break;
            case 'pointSearch':
                $path = $this->middlewareHelper->getPointSearchInterfaceId('website', $websiteId);
                break;
            case 'customerOrder':
                $path = $this->middlewareHelper->getCustomerSearchInterfaceId('website', $websiteId);
                break;
        }
        return $path;
    }
}
