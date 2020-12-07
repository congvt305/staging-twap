<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오후 1:58
 */

namespace Amore\PointsIntegration\Controller\Points;


use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Ajax extends AbstractController
{
    /**
     * @var \Amore\PointsIntegration\Model\RedeemPointsSearch
     */
    private $redeemPointsSearch;

    /**
     * Ajax constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param \Amore\PointsIntegration\Model\RedeemPointsSearch $redeemPointsSearch
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        \Amore\PointsIntegration\Model\RedeemPointsSearch $redeemPointsSearch
    ) {
        parent::__construct($context, $customerSession, $resultPageFactory);
        $this->redeemPointsSearch = $redeemPointsSearch;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $page = $this->getRequest()->getParam('page');

        $customer = $this->customerSession->getCustomer();
        if ($this->getRequest()->isAjax()) {
            /** @var Page $ajaxBlock */
            $ajaxBlock = $this->resultPageFactory->create();
            $ajaxBlock = $ajaxBlock->getLayout()->getBlock('redeem.points.ajax')->toHtml();
            $this->getResponse()->setBody($ajaxBlock);
        }
    }
}
