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
     * @param array $pageData
     * @return array
     */
    public function ajaxPagination(array $pageData): array
    {
        //Total number of page ranges
        $pageCnt = $pageData[0]['totPage'];
        //Number of posts per page
        $listSize = count($pageData);
        //Total number of posts
        $listCnt = $pageData[0]['totCnt'];
        //Number of pages to show
        $rangeSize = 5;
        //Current page
        $page = $pageData[0]['page'];
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

        $pageData[0]['pageCnt'] = $pageCnt;
        $pageData[0]['listSize'] = $listSize;
        $pageData[0]['listCnt'] = $listCnt;
        $pageData[0]['rangeSize'] = $rangeSize;
        $pageData[0]['range'] = $range;
        $pageData[0]['startPage'] = $startPage;
        $pageData[0]['endPage'] = $endPage > $pageCnt ? $pageCnt : $endPage;
        $pageData[0]['prev'] = $prev;
        $pageData[0]['next'] = $next;
        $pageData[0]['prevPage'] = $startPage - 1;
        $pageData[0]['nextPage'] = $endPage + 1;
        return $pageData;
    }
}
