<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-07
 * Time: 오전 10:02
 */

namespace Amore\PointsIntegration\ViewModel\Points;

use Amore\PointsIntegration\Model\PointsHistorySearch;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class HistoryAjax implements ArgumentInterface
{
    /**
     * @var RequestInterface
     */
    private $requestInterface;
    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var PointsHistorySearch
     */
    private $pointsHistorySearch;

    /**
     * HistoryAjax constructor.
     * @param RequestInterface $requestInterface
     * @param Session $customerSession
     * @param PointsHistorySearch $pointsHistorySearch
     */
    public function __construct(
        RequestInterface $requestInterface,
        Session $customerSession,
        PointsHistorySearch $pointsHistorySearch
    ) {
        $this->requestInterface = $requestInterface;
        $this->customerSession = $customerSession;
        $this->pointsHistorySearch = $pointsHistorySearch;
    }

    public function getPageData()
    {
        $customer = $this->customerSession->getCustomer();

        $page = $this->requestInterface->getParam('page');

        return $this->pointsHistorySearch->getPointsHistoryResult($customer->getId(), $customer->getWebsiteId(), $page);

//        return [
//            [
//                'totCnt' => 10,
//                'totPage' => 1,
//                'date' => '2020-11-11',
//                'typeCD' => '적립/사용 type',
//                'typeNM' => '적립/사용 type name',
//                'point' => '111111111111',
//                'validPeriod' => '2022-11-13',
//                'reason' => "그냥 사용1",
//            ],[
//                'totCnt' => 10,
//                'totPage' => 1,
//                'date' => '2020-11-11',
//                'typeCD' => '적립/사용 type',
//                'typeNM' => '적립/사용 type name',
//                'point' => '111111111111',
//                'validPeriod' => '2022-11-13',
//                'reason' => "그냥 사용2",
//            ],[
//                'totCnt' => 10,
//                'totPage' => 1,
//                'date' => '2020-11-11',
//                'typeCD' => '적립/사용 type',
//                'typeNM' => '적립/사용 type name',
//                'point' => '111111111111',
//                'validPeriod' => '2022-11-13',
//                'reason' => "그냥 사용3",
//            ],[
//                'totCnt' => 10,
//                'totPage' => 1,
//                'date' => '2020-11-11',
//                'typeCD' => '적립/사용 type',
//                'typeNM' => '적립/사용 type name',
//                'point' => '111111111111',
//                'validPeriod' => '2022-11-13',
//                'reason' => "그냥 사용4",
//            ],[
//                'totCnt' => 10,
//                'totPage' => 1,
//                'date' => '2020-11-11',
//                'typeCD' => '적립/사용 type',
//                'typeNM' => '적립/사용 type name',
//                'point' => '111111111111',
//                'validPeriod' => '2022-11-13',
//                'reason' => "그냥 사용5",
//            ]
//        ];
    }
}
