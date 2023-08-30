<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오전 11:32
 */

namespace Amore\PointsIntegration\Model;

use CJ\Middleware\Model\PosRequest;
class RedeemPointsSearch extends PosRequest
{
    /**
     * @param $customerId
     * @param $websiteId
     * @param $page
     * @return array|bool|float|int|mixed|string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRedeemSearchResult($customerId, $websiteId, $page = 1)
    {
        $requestData = $this->middlewareHelper->getRequestDataByCustomerId($customerId, $page);
        return $this->sendRequest($requestData, $websiteId, 'redeemSearch');
    }
}
