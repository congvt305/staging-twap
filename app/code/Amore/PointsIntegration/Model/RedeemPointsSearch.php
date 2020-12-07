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
//        $test = [
//            [
//                'totCnt' => 10,
//                'totPage' => 10,
//                'page' => 1,
//                'prdCd' => 'product_code',
//                'prdNM' => 'product_name',
//                'redeemDate' => 'buy_date',
//                'redeemStoreCD' => 'buy_store code',
//                'redeemStoreNM' => 'buy store name',
//                'redeemQty' => 2,
//                'usePoint' => 100
//            ],[
//                'totCnt' => 10,
//                'totPage' => 10,
//                'page' => 1,
//                'prdCd' => 'product_code2',
//                'prdNM' => 'product_name2',
//                'redeemDate' => 'buy_date2',
//                'redeemStoreCD' => 'buy_store code',
//                'redeemStoreNM' => 'buy store name',
//                'redeemQty' => 1,
//                'usePoint' => 200
//            ],[
//                'totCnt' => 10,
//                'totPage' => 10,
//                'page' => 1,
//                'prdCd' => 'product_code3',
//                'prdNM' => 'product_name3',
//                'redeemDate' => 'buy_date3',
//                'redeemStoreCD' => 'buy_store code',
//                'redeemStoreNM' => 'buy store name',
//                'redeemQty' => 3,
//                'usePoint' => 300
//            ],[
//                'totCnt' => 10,
//                'totPage' => 10,
//                'page' => 1,
//                'prdCd' => 'product_code4',
//                'prdNM' => 'product_name4',
//                'redeemDate' => 'buy_date4',
//                'redeemStoreCD' => 'buy_store code',
//                'redeemStoreNM' => 'buy store name',
//                'redeemQty' => 1,
//                'usePoint' => 400
//            ],[
//                'totCnt' => 10,
//                'totPage' => 10,
//                'page' => 1,
//                'prdCd' => 'product_code5',
//                'prdNM' => 'product_name5',
//                'redeemDate' => 'buy_date5',
//                'redeemStoreCD' => 'buy_store code',
//                'redeemStoreNM' => 'buy store name',
//                'redeemQty' => 1,
//                'usePoint' => 500
//            ]
//        ];
//
//        return $test;
//        return $this->json->serialize($test);
    }
}
