<?php

namespace CJ\Middleware\Model;

/**
 * Class PosRequest
 *
 * @package \Amore\PointsIntegration\Model
 */
class SapRequest extends BaseRequest
{
    public function handleResponse($response)
    {
        return parent::handleResponse($response);
    }

    public function responseValidation($response, $websiteId)
    {
        return parent::responseValidation($response, $websiteId);
    }
}