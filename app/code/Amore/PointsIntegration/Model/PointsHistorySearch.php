<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오전 11:26
 */

namespace Amore\PointsIntegration\Model;

class PointsHistorySearch extends AbstractPointsModel
{
    public function getPointsHistoryResult($customerId, $websiteId, $page = 1)
    {
        $requestData = $this->requestData($customerId, $page);
        return $this->request->sendRequest($requestData, $websiteId, 'pointSearch');

//        $test = [
//        [
//            'totCnt' => 10,
//            'totPage' => 1,
//            'date' => '2020-11-11',
//            'typeCD' => '적립/사용 type',
//            'typeNM' => '적립/사용 type name',
//            'point' => '111111111111',
//            'validPeriod' => '2022-11-13',
//            'reason' => "그냥 사용1",
//        ],[
//            'totCnt' => 10,
//            'totPage' => 1,
//            'date' => '2020-11-11',
//            'typeCD' => '적립/사용 type',
//            'typeNM' => '적립/사용 type name',
//            'point' => '111111111111',
//            'validPeriod' => '2022-11-13',
//            'reason' => "그냥 사용2",
//        ],[
//            'totCnt' => 10,
//            'totPage' => 1,
//            'date' => '2020-11-11',
//            'typeCD' => '적립/사용 type',
//            'typeNM' => '적립/사용 type name',
//            'point' => '111111111111',
//            'validPeriod' => '2022-11-13',
//            'reason' => "그냥 사용3",
//        ],[
//            'totCnt' => 10,
//            'totPage' => 1,
//            'date' => '2020-11-11',
//            'typeCD' => '적립/사용 type',
//            'typeNM' => '적립/사용 type name',
//            'point' => '111111111111',
//            'validPeriod' => '2022-11-13',
//            'reason' => "그냥 사용4",
//        ],[
//            'totCnt' => 10,
//            'totPage' => 1,
//            'date' => '2020-11-11',
//            'typeCD' => '적립/사용 type',
//            'typeNM' => '적립/사용 type name',
//            'point' => '111111111111',
//            'validPeriod' => '2022-11-13',
//            'reason' => "그냥 사용5",
//        ]
//    ];
//
//        return $test;
    }
}
