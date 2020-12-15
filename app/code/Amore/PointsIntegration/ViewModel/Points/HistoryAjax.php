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
     * @var \Amore\PointsIntegration\Model\Pagination
     */
    private $pagination;
    /**
     * @var \Amore\PointsIntegration\Model\CustomerPointsSearch
     */
    private $customerPointsSearch;

    /**
     * HistoryAjax constructor.
     * @param RequestInterface $requestInterface
     * @param Session $customerSession
     * @param PointsHistorySearch $pointsHistorySearch
     * @param \Amore\PointsIntegration\Model\Pagination $pagination
     * @param \Amore\PointsIntegration\Model\CustomerPointsSearch $customerPointsSearch
     */
    public function __construct(
        RequestInterface $requestInterface,
        Session $customerSession,
        PointsHistorySearch $pointsHistorySearch,
        \Amore\PointsIntegration\Model\Pagination $pagination,
        \Amore\PointsIntegration\Model\CustomerPointsSearch $customerPointsSearch
    ) {
        $this->requestInterface = $requestInterface;
        $this->customerSession = $customerSession;
        $this->pointsHistorySearch = $pointsHistorySearch;
        $this->pagination = $pagination;
        $this->customerPointsSearch = $customerPointsSearch;
    }

    public function getPageData()
    {
        $customer = $this->customerSession->getCustomer();

        $page = $this->requestInterface->getParam('page');

        $pointsHistoryResult = $this->pointsHistorySearch->getPointsHistoryResult($customer->getId(), $customer->getWebsiteId(), $page);

        if ($this->responseValidation($pointsHistoryResult)) {
            $pointsData = $pointsHistoryResult['data']['point_data'];
            return $this->pagination->ajaxPagination($pointsData);
        } else {
            return [];
        }
    }

    public function getCustomerPointsResulst()
    {
        $customer = $this->customerSession->getCustomer();
        $customerPointsResult = $this->customerPointsSearch->getMemberSearchResult($customer->getId(), $customer->getWebsiteId());

        if ($this->responseValidation($customerPointsResult)) {
            return $customerPointsResult['data'];
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
