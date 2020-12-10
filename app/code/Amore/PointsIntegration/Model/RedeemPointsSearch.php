<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오전 11:32
 */

namespace Amore\PointsIntegration\Model;

class RedeemPointsSearch extends AbstractPointsModel
{
    public function getRedeemSearchResult($customerId, $websiteId, $page = 1)
    {
        $requestData = $this->requestData($customerId, $page);
        return $this->request->sendRequest($requestData, $websiteId, 'redeemSearch');
    }
}
