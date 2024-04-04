<?php

namespace CJ\Middleware\Model;

use Amore\PointsIntegration\Model\Source\Config;
use CJ\Middleware\Helper\Data as MiddlewareHelper;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class SapRequest
 *
 * @package \CJ\Middleware\Model
 */
class SapRequest extends BaseRequest
{
    /**
     * @param Curl $curl
     * @param MiddlewareHelper $middlewareHelper
     * @param Logger $logger
     * @param Config $config
     */
    public function __construct(Curl $curl, MiddlewareHelper $middlewareHelper, Logger $logger, Config $config)
    {
        parent::__construct($curl, $middlewareHelper, $logger, $config);
    }

    /**
     * @param $response
     * @param $type
     * @return array|null
     */
    public function handleResponse($response, $type = 'confirm')
    {
        $resultSize = count($response);
        if ($resultSize > 0) {
            $success = false;
            $message = '';
            $data = [];

            if (isset($response['success'])) {
                $success = $response['success'];
            }

            if (isset($response['data'], $response['data']['response'])) {
                $data = $response['data']['response'];
            }

            if (isset($response['data']['response']['header']['rtn_MSG'])) {
                $message = $response['data']['response']['header']['rtn_MSG'];
            }

            return [
                'success' => $success,
                'data' => $data,
                'message' => $message,
            ];
        }

        return null;
    }
}