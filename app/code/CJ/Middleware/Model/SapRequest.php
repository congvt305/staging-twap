<?php

namespace CJ\Middleware\Model;

/**
 * Class SapRequest
 *
 * @package \CJ\Middleware\Model
 */
class SapRequest extends BaseRequest
{
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