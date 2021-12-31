<?php

namespace CJ\CouponCustomer\Block\Coupon;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Header\Logo;
use Magento\Theme\Block\Html\Pager;
use Magento\SalesRule\Model\Rule;

class Index extends Template
{
    /**
     * @var RuleCollection
     */
    private $ruleCollection;
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Logo
     */
    private $logo;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    private $rule;

    /**
     * @param Template\Context $context
     * @param array $data
     * @param RuleCollection $ruleCollection
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param Logo $logo
     */
    public function __construct(Template\Context      $context, array $data = [],
                                RuleCollection        $ruleCollection,
                                Session               $customerSession,
                                StoreManagerInterface $storeManager,
                                Logo                  $logo,
                                Rule                  $rule

    )
    {
        parent::__construct($context, $data);
        $this->ruleCollection = $ruleCollection;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->logo = $logo;
        $this->rule = $rule;
    }

    /**
     * @return \Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    public function getRuleCollection()
    {
        $rules = $this->ruleCollection->create();
        $customer = $this->getCustomer();
        $websiteId = $customer->getWebsiteId();
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 4;
        $rules->addWebsiteGroupDateFilter($websiteId, $customer->getGroupId())
            ->addFieldToFilter('coupon_type', 2)
            ->addFieldToFilter('is_active', 1)
            ->setPageSize($pageSize)
            ->setCurPage($page);
        return $rules;
    }

    /**
     * prepare layout
     *
     * @return \Eguana\CustomerBulletin\Block\Index\Index|Index
     */
    protected function _prepareLayout()
    {
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
                'coupon',
                [
                    'label' => __('Coupon Wallet'),
                    'title' => __('Coupon Wallet'),
                ]
            );
        }
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Coupon Wallet'));
        if ($this->getRuleCollection()) {
            $pager = $this->getLayout()->createBlock(
                Pager::class,
                'custom.history.pager'
            )->setAvailableLimit([4 => 4, 8 => 8, 12 => 12, 16 => 16])
                ->setShowPerPage(true)->setCollection(
                    $this->getRuleCollection()
                );
            $this->setChild('pager', $pager);
            $this->getRuleCollection()->load();
        }
        parent::_prepareLayout();
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml(): string
    {
        return $this->getChildHtml('pager');
    }


    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }

    /**
     * @return string
     */
    public function getLogo()
    {
        return $this->logo->getLogoSrc();
    }

    public function getCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    public function getRuleCondition($ruleId)
    {
        return $this->rule->load($ruleId)->getConditions()->getAggregatorName();

    }

}
