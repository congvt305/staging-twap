<?php
namespace Amore\Base\Model;

use Amore\Base\Logger\Logger as AmoreLogger;
use \Amore\Base\Model\BaseRequest as BaseRequestAbstract;
use CJ\Middleware\Helper\Data as MiddlewareHelper;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\Serializer\Json;

class Request extends BaseRequestAbstract
{
    /**
     * @param Curl $curl
     * @param Json $json
     * @param MiddlewareHelper $middlewareHelper
     * @param AmoreLogger $amoreLogger
     */
    public function __construct(
        Curl $curl,
        Json $json,
        MiddlewareHelper $middlewareHelper,
        AmoreLogger $amoreLogger
    )
    {
        parent::__construct(
            $curl,
            $json,
            $middlewareHelper,
            $amoreLogger)
        ;
    }

    /**
     * @param string $url
     * @param array|string $requestData
     * @param $websiteId
     * @param string $scope
     * @param string $type
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function send(
        string $url,
        $requestData,
        $websiteId,
        string $scope = 'store',
        string $type = 'confirm'
    ): string
    {
        return parent::send($url, $requestData, $websiteId, $scope, $type);
    }

    /**
     * @param string $url
     * @param array $requestData
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processCurl(
        string $url,
        array $requestData = []
    ): string
    {
        return parent::processCurl($url, $requestData);

    }

}
