<?php
declare(strict_types=1);

namespace CJ\Coupons\ViewModel;

/**
 * Class CouponList
 */
class CouponList implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $ruleCollection;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Theme\Block\Html\Header\Logo
     */
    protected $logo;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $currency;

    /**
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Theme\Block\Html\Header\Logo $logo
     */
    public function __construct(
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Theme\Block\Html\Header\Logo $logo,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->storeManager = $storeManager;
        $this->ruleCollection = $ruleCollection;
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->logo = $logo;
        $this->currency = $currency;
    }

    /**
     * Get rule collection
     *
     * @return \Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    public function getRuleCollection(): \Magento\SalesRule\Model\ResourceModel\Rule\Collection
    {
        $rules = $this->ruleCollection->create();
        $customer = $this->customerSession->getCustomer();
        $websiteId = $customer->getWebsiteId();
        $page = ($this->request->getParam('p')) ? $this->request->getParam('p') : 1;
        $pageSize = ($this->request->getParam('limit')) ? $this->request->getParam('limit') : 6;
        $rules->addWebsiteGroupDateFilter($websiteId, $customer->getGroupId())
            ->addFieldToFilter('coupon_type', \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC)
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('use_auto_generation', 0)
            ->addFieldToFilter('is_active_wallet', 1)
            ->setPageSize($pageSize)
            ->setCurPage($page);
        return $rules;
    }

   public function getLogo() :string {
       return $this->logo->getLogoSrc();
   }

    /**
     * Get currency code
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrencyCode()
    {
        return $this->currency->getCurrencySymbol();
    }
}
