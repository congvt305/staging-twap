<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-07
 * Time: 오전 9:58
 */

namespace Amore\PointsIntegration\Block\Points;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;

class PointsHistorySearch extends AbstractPointsBlock
{
    /**
     * @var \Amore\PointsIntegration\Model\PointsHistorySearch
     */
    private $pointsHistorySearch;

    /**
     * PointsHistorySearch constructor.
     * @param Template\Context $context
     * @param Session $customerSession
     * @param \Amore\PointsIntegration\Model\PointsHistorySearch $pointsHistorySearch
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $customerSession,
        \Amore\PointsIntegration\Model\PointsHistorySearch $pointsHistorySearch,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $data);
        $this->pointsHistorySearch = $pointsHistorySearch;
    }

    public function getPointsHistoryResult()
    {
        $customer = $this->getCustomer();

        return $this->pointsHistorySearch->getPointsHistoryResult($customer->getId(), $customer->getWebsiteId(), 1);
    }
}
