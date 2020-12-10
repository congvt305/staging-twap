<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오후 1:25
 */

namespace Amore\PointsIntegration\ViewModel\Points;

use Amore\PointsIntegration\Model\RedeemPointsSearch;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class RedeemAjax implements ArgumentInterface
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
     * @var RedeemPointsSearch
     */
    private $redeemPointsSearch;

    /**
     * RedeemAjax constructor.
     * @param RequestInterface $requestInterface
     * @param Session $customerSession
     * @param RedeemPointsSearch $redeemPointsSearch
     */
    public function __construct(
        RequestInterface $requestInterface,
        Session $customerSession,
        RedeemPointsSearch $redeemPointsSearch
    ) {
        $this->requestInterface = $requestInterface;
        $this->customerSession = $customerSession;
        $this->redeemPointsSearch = $redeemPointsSearch;
    }

    public function getPageData()
    {
        $customer = $this->customerSession->getCustomer();

        $page = $this->requestInterface->getParam('page');

        return $this->redeemPointsSearch->getRedeemSearchResult($customer->getId(), $customer->getWebsiteId(), $page);

//        return [
//            [
//                'totCnt' => 10,
//                'page' => 1,
//                'prdCd' => 'test1',
//                'prdNM' => 'test1',
//                'redeemDate' => 'buy_date',
//                'redeemStoreCD' => 'buy_store code',
//                'redeemStoreNM' => 'buy store name',
//                'redeemQty' => 2,
//                'usePoint' => 100
//            ],[
//                'totCnt' => 10,
//                'page' => 1,
//                'prdCd' => 'test2',
//                'prdNM' => 'test2',
//                'redeemDate' => 'buy_date2',
//                'redeemStoreCD' => 'buy_store code',
//                'redeemStoreNM' => 'buy store name',
//                'redeemQty' => 1,
//                'usePoint' => 200
//            ],[
//                'totCnt' => 10,
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
    }
}
