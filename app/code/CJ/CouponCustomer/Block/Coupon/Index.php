<?php

namespace CJ\CouponCustomer\Block\Coupon;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Header\Logo;
use Magento\Theme\Block\Html\Pager;
use \Magento\Directory\Model\Currency;
use CJ\CouponCustomer\Helper\Data;

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

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var
     */
    protected $helperData;

    /**
     * const pos customer grade
     */
    const POS_CUSTOMER_GRADE = 'pos_customer_grade';


    /**
     * @param Template\Context $context
     * @param array $data
     * @param RuleCollection $ruleCollection
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param Logo $logo
     */
    public function __construct(
        Template\Context      $context,
        array                 $data = [],
        RuleCollection        $ruleCollection,
        Session               $customerSession,
        StoreManagerInterface $storeManager,
        Logo                  $logo,
        Currency $currency,
        Data $helperData

    ){
        parent::__construct($context, $data);
        $this->ruleCollection = $ruleCollection;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->logo = $logo;
        $this->currency = $currency;
        $this->helperData = $helperData;
    }

    /**
     * @return \Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    public function getRuleCollection()
    {
        $rules = $this->ruleCollection->create();
        $customer = $this->getCustomer();
        $posCustomerGroup = $customer->getData(self::POS_CUSTOMER_GRADE);
        $posCustomerGroupId = $this->helperData->getPOSCustomerGroupIdByName($posCustomerGroup);
        $websiteId = $customer->getWebsiteId();
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 6;
        $rules->addWebsiteGroupDateFilter($websiteId, $posCustomerGroupId)
            ->addFieldToFilter('coupon_type', \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC)
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('use_auto_generation', 0)
            ->setPageSize($pageSize)
            ->setCurPage($page);
        return $rules;
    }

    /**
     * @return $this|Index
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        if ($this->getRuleCollection()->count()) {
            $pager = $this->getLayout()->createBlock(
                Pager::class,
                'customer.coupon.pager'
            )->setAvailableLimit([6 => 6, 12 => 12, 18 => 18, 24 => 24])
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

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrencyCode()
    {
        return $this->currency->getCurrencySymbol();
    }
}
