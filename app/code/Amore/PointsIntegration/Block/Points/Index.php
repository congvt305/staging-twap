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
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Index extends AbstractPointsBlock
{
    /**
     * @var \Amore\PointsIntegration\Model\CustomerPointsSearch
     */
    private $customerPointsSearch;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

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
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $customerSession,
        \Amore\PointsIntegration\Model\CustomerPointsSearch $customerPointsSearch,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $data);
        $this->customerPointsSearch = $customerPointsSearch;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    public function getPointsSearchResult()
    {
        $customer = $this->getCustomer();

        return $this->customerPointsSearch->getMemberSearchResult($customer->getId(), $customer->getWebsiteId());
    }

    protected function _prepareLayout()
    {
        try {
            if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbsBlock->addCrumb(
                    'home',
                    [
                        'label' => __('Home'),
                        'title' => __('Go to Home Page'),
                        'link' => $this->storeManager->getStore()->getBaseUrl()
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'account',
                    [
                        'label' => __('My Account'),
                        'title' => __('My Account'),
                        'link' => $this->storeManager->getStore()->getBaseUrl() . 'customer/account/'
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'tickets',
                    [
                        'label' => __('Customer Points'),
                        'title' => __('Customer Points'),
                    ]
                );
            }
            parent::_prepareLayout();
            $this->pageConfig->getTitle()->set(__('Customer Points'));
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        parent::_prepareLayout();
        return $this;
    }
}
