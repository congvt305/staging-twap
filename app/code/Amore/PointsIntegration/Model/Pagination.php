<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/12/14
 * Time: 2:53 PM
 */

namespace Amore\PointsIntegration\Model;

class Pagination
{
    /**
     * @param array $redeemPointsResult
     * @return array
     */
    public function ajaxPagination(array $redeemPointsResult): array
    {
        //Total number of page ranges
        $pageCnt = $redeemPointsResult[0]['totPage'];
        //Number of posts per page
        $listSize = count($redeemPointsResult);
        //Total number of posts
        $listCnt = $redeemPointsResult[0]['totCnt'];
        //Number of pages to show
        $rangeSize = 5;
        //Current page
        $page = $redeemPointsResult[0]['page'];
        //Current page range
        $range = ceil($page / $rangeSize);
        //Start page
        $startPage = ($range - 1) * $rangeSize + 1;
        //End page
        $endPage = $range * $rangeSize;
        //Prev page
        $prev = $range == 1 ? false : true;
        //Next page
        $next = $endPage > $pageCnt ? false : true;

        $redeemPointsResult[0]['pageCnt'] = $pageCnt;
        $redeemPointsResult[0]['listSize'] = $listSize;
        $redeemPointsResult[0]['listCnt'] = $listCnt;
        $redeemPointsResult[0]['rangeSize'] = $rangeSize;
        $redeemPointsResult[0]['range'] = $range;
        $redeemPointsResult[0]['startPage'] = $startPage;
        $redeemPointsResult[0]['endPage'] = $endPage > $pageCnt ? $pageCnt : $endPage;
        $redeemPointsResult[0]['prev'] = $prev;
        $redeemPointsResult[0]['next'] = $next;
        $redeemPointsResult[0]['prevPage'] = $startPage - 1;
        $redeemPointsResult[0]['nextPage'] = $endPage + 1;
        return $redeemPointsResult;
    }
}
