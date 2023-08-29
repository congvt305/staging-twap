<?php

namespace CJ\Middleware\Model;

/**
 * Class PosRequest
 *
 * @package \Amore\PointsIntegration\Model
 */
class PosRequest extends BaseRequest
{

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
                $result = $this->prepareResponseForPosRequest($response);
                break;
            case 'pointUpdate':
                $result = $this->prepareResponseForPointUpdate($response);
                break;
            default:
                $result = [];
        }

        return $result;
    }

    /**
     * @param $response
     * @param $websiteId
     * @param $type
     * @return bool
     */
    public function responseValidation($response, $websiteId, $type = null)
    {
        $responseHandled = $this->handleResponse($response, $type);
        return !empty($responseHandled);
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

        if (isset($response['data']['statusCode'])) {
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
}