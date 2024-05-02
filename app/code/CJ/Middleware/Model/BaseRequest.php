<?php
namespace CJ\Middleware\Model;

use CJ\Middleware\Helper\Data as MiddlewareHelper;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\HTTP\Client\Curl;
use Amore\PointsIntegration\Model\Source\Config;

abstract class BaseRequest
{
    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var MiddlewareHelper
     */
    protected $middlewareHelper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Request constructor.
     * @param Curl $curl
     * @param MiddlewareHelper $middlewareHelper
     * @param Logger $logger
     * @param Config $config
     */
    public function __construct(
        Curl $curl,
        MiddlewareHelper $middlewareHelper,
        Logger $logger,
        Config $config
    ) {
        $this->curl = $curl;
        $this->middlewareHelper = $middlewareHelper;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @param $requestData
     * @param $websiteId
     * @param $type
     * @return array|bool|float|int|mixed|string|null
     */
    public function sendRequest($requestData, $websiteId, $type)
    {
        $isMiddlewareEnable = $this->middlewareHelper->isMiddlewareEnabled('website', $websiteId);
        $url = $this->middlewareHelper->getMiddlewareURL('website', $websiteId);
        if (!$url && !$isMiddlewareEnable) {
            $this->logger->info("Url or Enable New Middleware is empty. Please check configuration and try again.");
            return [];
        }

        try {
            $this->curl->setOptions([
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HTTPHEADER => [
                    'Content-type: application/json'
                ],
            ]);
            if ($this->config->getSSLVerification($websiteId)) {
                $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, false);
                $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
            }

            $response = $this->sendToMiddleware($requestData, 'website', $websiteId, $type);
            return $this->middlewareHelper->unserializeData($response);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            return [];
        }
    }

    /**
     * @param $requestData
     * @param $scope
     * @param $websiteId
     * @param $type
     * @return string
     */
    protected function sendToMiddleware($requestData, $scope = 'store', $websiteId = null, $type = 'confirm')
    {
        $isMiddlewareEnable = $this->middlewareHelper->isMiddlewareEnabled($scope, $websiteId);
        $logEnabled = $this->config->getLoggerActiveCheck($websiteId);
        if ($isMiddlewareEnable) {
            $url = $this->middlewareHelper->getMiddlewareURL($scope, $websiteId);
            $modifiedRequestData = $this->prepareRequestData($requestData, $scope, $websiteId, $type, $logEnabled);
            $response = $this->callMiddlewareApi($url, $modifiedRequestData, $logEnabled);
            return $response;
        }
        $this->logger->error("Please enable config Enabled New Middleware System to send data to Middleware.");
        return '';
    }

    /**
     * Get Interface Id for replacing path corresponding
     * @param $scope
     * @param $websiteId
     * @param $type
     * @return mixed
     */
    protected function getInterfaceID($scope, $websiteId, $type)
    {
        switch ($type) {
            case 'memberSearch':
                $path = $this->middlewareHelper->getMemberSearchInterfaceId($scope, $websiteId);
                break;
            case 'redeemSearch':
                $path = $this->middlewareHelper->getRedeemSearchInterfaceId($scope, $websiteId);
                break;
            case 'pointSearch':
                $path = $this->middlewareHelper->getPointSearchInterfaceId($scope, $websiteId);
                break;
            case 'pointUpdate':
                $path = $this->middlewareHelper->getPointUpdateInterfaceId($scope, $websiteId);
                break;
            case 'customerOrder':
                $path = $this->middlewareHelper->getCustomerSearchInterfaceId($scope, $websiteId);
                break;
            case 'confirm':
                $path = $this->middlewareHelper->getOrderConfirmInterfaceId($scope, $websiteId);
                break;
            case 'cancel':
                $path = $this->middlewareHelper->getOrderCancelInterfaceId($scope, $websiteId);
                break;
            case 'memberInfo':
                $path = $this->middlewareHelper->getMemberInfoInterfaceId($scope, $websiteId);
                break;
            case 'memberJoin':
                $path = $this->middlewareHelper->getMemberJoinInterfaceId($scope, $websiteId);
                break;
            case 'baInfo':
                $path = $this->middlewareHelper->getBacodeInfoInterfaceId($scope, $websiteId);
                break;
            default:
                $path = $this->middlewareHelper->getOrderConfirmInterfaceId($scope, $websiteId);
        }
        return $path;
    }

    /**
     * @param $response
     * @return bool
     */
    public function responseValidation($response)
    {
        if (isset($response['data']['statusCode']) && $response['data']['statusCode'] == '200') {
            return true;
        }
        return false;
    }

    /**
     * @param $requestData
     * @param $scope
     * @param $websiteId
     * @param $type
     * @return bool|string
     */
    protected function prepareRequestData($requestData, $scope = 'store', $websiteId = null, $type = 'confirm', $logEnabled = true)
    {
        if (!is_array($requestData)) {
            $requestData = $this->middlewareHelper->unserializeData($requestData);
        }

        $requestData['API_ID'] = $this->getInterfaceID($scope, $websiteId, $type);
        $requestData['API_USER_ID'] = $this->middlewareHelper->getMiddlewareUsername($scope, $websiteId);
        $requestData['AUTH_KEY'] = $this->middlewareHelper->getMiddlewareAuthKey($scope, $websiteId);
        $requestData['salOrgCd'] = $this->middlewareHelper->getSalesOrganizationCode($scope, $websiteId);
        $requestData['salOffCd'] = $this->middlewareHelper->getSalesOfficeCode($scope, $websiteId);

        $modifiedRequestData = $this->middlewareHelper->serializeData($requestData);
        if ($logEnabled){
            $this->logger->info("=====Submit request=====");
            $this->logger->info($modifiedRequestData);
        }

        return $modifiedRequestData;
    }

    /**
     * @param $url
     * @param $modifiedRequestData
     * @return string
     */
    protected function callMiddlewareApi($url, $modifiedRequestData, $logEnabled = true){
        $this->curl->setTimeout(60);
        $this->curl->post($url, $modifiedRequestData);
        $response = $this->curl->getBody();
        if ($logEnabled){
            $this->logger->info("=====Response by base request=====");
            $this->logger->info($response);
        }
        return $response;
    }

    /**
     * @param $response
     * @param $storeId
     * @return array|null
     */
    public function handleResponse($response)
    {
        $resultSize = count($response);
        if ($resultSize > 0) {
            $success = true;
            $status = false;
            $message = '';
            if ($response['success'] != true) {
                $success = $response['success'];
                $message = $response['data']['message'];
            }

            if ((isset($response['success']) && $response['success']) &&
                (isset($response['data']['statusCode']) && $response['data']['statusCode'] == '200')
            ) {
                $status = true;
            }

            return [
                'success' => $success,
                'data' => $response['data']['response'],
                'message' => $message,
                'status' => $status
            ];
        } else {
            return null;
        }
    }
}
