<?php

namespace CJ\Middleware\Model;

use Amore\PointsIntegration\Model\Source\Config;
use CJ\Middleware\Helper\Data as MiddlewareHelper;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class PosRequest
 *
 * @package \CJ\Middleware\Model
 */
class PosRequest extends BaseRequest
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
     * @param $storeId
     * @param $type
     */
    public function handleResponse($response, $type = null)
    {
        switch ($type) {
            case 'memberSearch':
            case 'redeemSearch':
            case 'pointSearch':
            case 'customerOrder':
            case 'memberJoin':
                $result = $this->prepareResponseForPosRequest($response);
                break;
            case 'pointUpdate':
                $result = $this->prepareResponseForPointUpdate($response);
                break;
            case 'memberInfo':
                $result = $this->prepareResponseForMemberInfo($response);
                break;
            default:
                $result = [];
        }

        return $result;
    }

    /**
     * @param $response
     * @return array
     */
    public function prepareResponseForPosRequest($response)
    {
        $success = false;
        $status = false;
        $message = '';
        $data = [];

        if (isset($response['success'])) {
            $success = $response['success'];
        }

        if (isset($response['data'])) {
            $data = $response['data'];
        }

        if (isset($response['data']['statusMessage'])) {
            $message = $response['data']['statusMessage'];
        }

        if (isset($response['data']['statusCode']) && $response['data']['statusCode'] == '200') {
            $status = true;
        }

        return [
            'success' => $success,
            'data' => $data,
            'message' => $message,
            'status' => $status
        ];
    }

    /**
     * @param $response
     * @return false
     */
    public function prepareResponseForPointUpdate($response)
    {
        $data = $response['data'] ?? null;

        if (!$data) {
            return false;
        }
        $message = $data['statusCode'] ?? null;
        if (($message == 'S') || ($message == 'E' && $data['statusMessage'] == 'The points have already been reflected.')) {
            return $data;
        }
        return false;
    }

    /**
     * @param $response
     * @return array
     */
    public function prepareResponseForMemberInfo($response)
    {
        if ((isset($response['success']) && $response['success']) &&
            (isset($response['data']) && isset($response['data']['checkYN']) && $response['data']['checkYN'] == 'Y')
        ) {
            $result = $this->processResponseData($response);
        } elseif ((isset($response['success']) && $response['success']) &&
            (isset($response['data']) && isset($response['data']['checkYN']) && $response['data']['checkYN'] == 'N')
        ) {
            $result = [];
        } else {
            if (isset($response['data']) &&
                isset($response['data']['statusMessage']) &&
                $response['data']['statusMessage']
            ) {
                $result['message'] = $response['data']['statusMessage'];
            } else {
                $result = [];
            }
        }

        return $result;
    }
}