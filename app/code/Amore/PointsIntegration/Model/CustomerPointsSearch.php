<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오전 11:27
 */

namespace Amore\PointsIntegration\Model;

use CJ\Middleware\Model\PosRequest;

class CustomerPointsSearch extends PosRequest
{
    /**
     * @param $customerId
     * @param $websiteId
     * @return array|bool|float|int|mixed|string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMemberSearchResult($customerId, $websiteId)
    {
        $requestData = $this->middlewareHelper->getRequestDataByCustomerId($customerId);
        return $this->sendRequest($requestData, $websiteId, 'memberSearch');
    }
}
