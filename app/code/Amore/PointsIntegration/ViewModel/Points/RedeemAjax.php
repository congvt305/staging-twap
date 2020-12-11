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

        $redeemPointsResult = $this->redeemPointsSearch->getRedeemSearchResult($customer->getId(), $customer->getWebsiteId(), $page);

        if ($this->responseValidation($redeemPointsResult)) {
            return $redeemPointsResult['data']['redemption_data'];
        } else {
            return [];
        }
    }

    public function responseValidation($response)
    {
        if (isset($response['data']['statusCode']) && $response['data']['statusCode'] == '200') {
            return true;
        } else {
            return false;
        }
    }
}
