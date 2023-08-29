<?php

namespace CJ\Middleware\Model;

/**
 * Class PosRequest
 *
 * @package \Amore\PointsIntegration\Model
 */
class PosRequest extends BaseRequest
{
    public function handleResponse($response, $storeId)
    {
        return parent::handleResponse($response, $storeId);
    }
    
    public function responseValidation($response, $websiteId)
    {
        return parent::responseValidation($response, $websiteId);
    }
}