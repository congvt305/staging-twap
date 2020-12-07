<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오전 11:48
 */

namespace Amore\PointsIntegration\Block\Points;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;

class Index extends AbstractPointsBlock
{
    /**
     * @var \Amore\PointsIntegration\Model\CustomerPointsSearch
     */
    private $customerPointsSearch;

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Points'));
    }

    /**
     * Index constructor.
     * @param Template\Context $context
     * @param Session $customerSession
     * @param \Amore\PointsIntegration\Model\CustomerPointsSearch $customerPointsSearch
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $customerSession,
        \Amore\PointsIntegration\Model\CustomerPointsSearch $customerPointsSearch,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $data);
        $this->customerPointsSearch = $customerPointsSearch;
    }

    public function getPointsSearchResult()
    {
        $customer = $this->getCustomer();

        return $this->customerPointsSearch->getMemberSearchResult($customer->getId(), $customer->getWebsiteId());
    }
}
