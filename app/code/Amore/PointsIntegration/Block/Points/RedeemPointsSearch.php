<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오전 9:50
 */

namespace Amore\PointsIntegration\Block\Points;

use Magento\Customer\Model\Session;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Pager;

class RedeemPointsSearch extends AbstractPointsBlock
{
    /**
     * @var \Amore\PointsIntegration\Model\RedeemPointsSearch
     */
    private $redeemPointsSearch;

    /**
     * RedeemPointsSearch constructor.
     * @param Template\Context $context
     * @param Session $customerSession
     * @param \Amore\PointsIntegration\Model\RedeemPointsSearch $redeemPointsSearch
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $customerSession,
        \Amore\PointsIntegration\Model\RedeemPointsSearch $redeemPointsSearch,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $data);
        $this->redeemPointsSearch = $redeemPointsSearch;
    }

    public function getPointsRedeemSearchResult()
    {
        $customer = $this->getCustomer();

        return $this->redeemPointsSearch->getRedeemSearchResult($customer->getId(), $customer->getWebsiteId(), 1);
    }
}
