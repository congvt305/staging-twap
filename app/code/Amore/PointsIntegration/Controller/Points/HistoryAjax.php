<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-07
 * Time: 오후 3:02
 */

namespace Amore\PointsIntegration\Controller\Points;


use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class HistoryAjax extends AbstractController
{
    /**
     * @var \Amore\PointsIntegration\Model\PointsHistorySearch
     */
    private $pointsHistorySearch;

    /**
     * HistoryAjax constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param \Amore\PointsIntegration\Model\PointsHistorySearch $pointsHistorySearch
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        \Amore\PointsIntegration\Model\PointsHistorySearch $pointsHistorySearch
    ) {
        parent::__construct($context, $customerSession, $resultPageFactory);
        $this->pointsHistorySearch = $pointsHistorySearch;
    }

    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            /** @var Page $ajaxBlock */
            $ajaxBlock = $this->resultPageFactory->create();
            $ajaxBlock = $ajaxBlock->getLayout()->getBlock('points.history.ajax')->toHtml();
            $this->getResponse()->setBody($ajaxBlock);
        }
    }
}
