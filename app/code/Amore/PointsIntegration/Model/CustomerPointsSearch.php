<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오전 11:27
 */

namespace Amore\PointsIntegration\Model;

class CustomerPointsSearch extends AbstractPointsModel
{
    /**
     * @param $customerId
     * @param $websiteId
     * @return array|bool|float|int|mixed|string|null
     */
    public function getMemberSearchResult($customerId, $websiteId)
    {
        $requestData = $this->requestData($customerId);
        return $this->request->sendRequest($requestData, $websiteId, 'memberSearch');
    }
}
