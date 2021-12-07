<?php
namespace Amore\Base\Model;

use CJ\Middleware\Helper\Data as MiddlewareHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;

abstract class BaseRequest
{
    /**
     * @var Curl
     */
    protected $curl;
    /**
     * @var Json
     */
    protected $json;

    /**
     * @var MiddlewareHelper
     */
    protected $middlewareHelper;

    /**
     * Request constructor.
     * @param Curl $curl
     * @param Json $json
     * @param MiddlewareHelper $middlewareHelper
     */
    public function __construct(
        Curl $curl,
        Json $json,
        MiddlewareHelper $middlewareHelper
    ) {
        $this->curl = $curl;
        $this->json = $json;
        $this->middlewareHelper = $middlewareHelper;
    }

    /**
     * Send request via middleware
     * @param $url
     * @param $requestData
     * @param string $scope
     * @param $websiteId
     * @param string $type
     * @return string
     */
    public function send($url, $requestData, $scope = 'store', $websiteId, $type = 'confirm')
    {
        $isNewMiddlewareEnable = $this->middlewareHelper->isNewMiddlewareEnabled('website', $websiteId);
        if ($isNewMiddlewareEnable) {
            if (!is_array($requestData)) {
                $requestData = $this->json->unserialize($requestData);
            }
            $url = $this->middlewareHelper->getNewMiddlewareURL($scope, $websiteId);
            $requestData['API_ID'] = $this->getInterfaceID($scope, $websiteId, $type);
            $requestData['API_USER_ID'] = $this->middlewareHelper->getMiddlewareUsername($scope, $websiteId);
            $requestData['AUTH_KEY'] = $this->middlewareHelper->getMiddlewareAuthKey($scope, $websiteId);
        }
        $this->curl->post($url, $this->json->serialize($requestData));

        return $this->curl->getBody();
    }

    /**
     * Get Interface Id for replacing path corresponding
     * @param $scope
     * @param $websiteId
     * @param $type
     * @return mixed
     */
    public function getInterfaceID($scope, $websiteId, $type)
    {
        $path = '';
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

}
