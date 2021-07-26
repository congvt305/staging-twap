<?php
/**
 * @author Eguana Team
 * Created by PhpStorm
 * User: Sonia Park
 * Date: 07/10/2021
 */

namespace Amore\GcrmBanner\Model\Banner;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Banner\Model\Config;

class Data implements SectionSourceInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Store Banner resource instance
     *
     * @var \Magento\Banner\Model\ResourceModel\Banner
     */
    protected $bannerResource;

    /**
     * Banner instance
     *
     * @var \Magento\Banner\Model\Banner
     */
    protected $banner;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * @var array
     */
    protected $banners = [];

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var array
     */
    private $bannersBySalesRule;

    /**
     * @var array
     */
    private $bannersByCatalogRule;
    /**
     * @var \Amore\GcrmBanner\Helper\Data
     */
    private $bannerHelper;

    /**
     * @var bool
     */
    private $isEnabled;
    /**
     * @var \Magento\Banner\Model\ResourceModel\Salesrule\CollectionFactory
     */
    private $salesruleCollectionFactory;


    /**
     * Data constructor.
     * @param \Amore\GcrmBanner\Helper\Data $bannerHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Banner\Model\ResourceModel\Salesrule\CollectionFactory $salesruleCollectionFactory
     * @param \Magento\Banner\Model\ResourceModel\Banner $bannerResource
     * @param \Magento\Banner\Model\Banner $banner
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        \Amore\GcrmBanner\Helper\Data $bannerHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Banner\Model\ResourceModel\Salesrule\CollectionFactory $salesruleCollectionFactory,
        \Magento\Banner\Model\ResourceModel\Banner $bannerResource,
        \Magento\Banner\Model\Banner $banner,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->bannerResource = $bannerResource;
        $this->banner = $banner;
        $this->storeManager = $storeManager;
        $this->httpContext = $httpContext;
        $this->filterProvider = $filterProvider;
        $this->storeId = $this->storeManager->getStore()->getId();
        $this->bannerHelper = $bannerHelper;
        $this->salesruleCollectionFactory = $salesruleCollectionFactory;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getSectionData()
    {
        return [
            'items' => [
                Config::BANNER_WIDGET_DISPLAY_SALESRULE => $this->getSalesRuleRelatedBanners(),
                Config::BANNER_WIDGET_DISPLAY_CATALOGRULE => $this->getCatalogRuleRelatedBanners(),
                Config::BANNER_WIDGET_DISPLAY_FIXED => $this->getFixedBanners(),
            ],
            'store_id' => $this->storeId
        ];
    }

    /**
     * Returns data for cart rule related banners applicable for the current session
     *
     * @return array
     */
    protected function getSalesRuleRelatedBanners()
    {
        return $this->getBannersData($this->getBannerIdsBySalesRules());
    }

    /**
     * Returns data for catalog rule related banners applicable for the current session
     *
     * @return array
     */
    protected function getCatalogRuleRelatedBanners()
    {
        return $this->getBannersData($this->getBannerIdsByCatalogRules());
    }

    /**
     * Returns data for active banners applicable for the current session
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    protected function getFixedBanners()
    {
        //add here check to load only active banners without assigned catalog rule and sales rule
        $bannersWithoutAssignedRules = $this->getActiveBannerIdsWithoutRelatedPromotions();

        $promotionsRelatedRules = array_merge_recursive(
            $this->getBannerIdsByCatalogRules(),
            $this->getBannerIdsBySalesRules()
        );
        $fixedBanners = array_merge_recursive($bannersWithoutAssignedRules, $promotionsRelatedRules);
        //merge here data from related catalogRules and related sales rules
        return $this->getBannersData($fixedBanners);
    }

    /**
     * Get real existing active banner ids which doesn't have assigned rules
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    private function getActiveBannerIdsWithoutRelatedPromotions()
    {
        $connection = $this->bannerResource->getConnection();
        $subSelect1 = $connection->select()->from(
            $this->bannerResource->getTable('magento_banner_catalogrule'),
            ['banner_id']
        );
        $subSelect2 = $connection->select()->from(
            $this->bannerResource->getTable('magento_banner_salesrule'),
            ['banner_id']
        );
        $sql = $this->bannerResource->getConnection()->select()->union(
            [
                $subSelect1,
                $subSelect2
            ],
            \Magento\Framework\DB\Select::SQL_UNION_ALL
        );
        $select = $connection->select()->from(
            $this->bannerResource->getMainTable(),
            ['banner_id']
        )->where(
            'is_enabled  = ?',
            1
        )->where(
            'banner_id not in (?)',
            $sql
        );

        return $connection->fetchCol($select);
    }

    /**
     * Get banners IDs that related to sales rule and satisfy conditions
     *
     * @return array
     */
    private function getBannerIdsBySalesRules() //todo if the module is enabled, then return all related banner ids.
    {
        if ($this->bannersBySalesRule === null) {
            $appliedRules = [];
            if ($this->checkoutSession->getQuoteId()) {
                $quote = $this->checkoutSession->getQuote();
                if ($quote && $quote->getAppliedRuleIds()) {
                    $appliedRules = explode(',', $quote->getAppliedRuleIds());
                }
            }
            $isEabled = $this->isEnabled();
            $this->bannersBySalesRule = $isEabled ? $this->getSalesRuleRelatedBannerIds()
                : $this->bannerResource->getSalesRuleRelatedBannerIds($appliedRules);
        }
        return $this->bannersBySalesRule;
    }

    private function getSalesRuleRelatedBannerIds()
    {
        $salesruleCollection = $this->salesruleCollectionFactory->create()
            ->addIsActiveSalesRuleFilter()
            ->getColumnValues('banner_id');
        return $salesruleCollection;
    }

    /**
     * Get banners IDs that related to catalog rule and satisfy conditions
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getBannerIdsByCatalogRules()
    {
        if ($this->bannersByCatalogRule === null) {
            $this->bannersByCatalogRule = $this->bannerResource->getCatalogRuleRelatedBannerIds(
                $this->storeManager->getWebsite()->getId(),
                $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP)
            );
        }
        return $this->bannersByCatalogRule;
    }

    /**
     * Returns banner data by identifier
     *
     * @param array $bannersIds
     * @return array
     * @throws \Exception
     */
    protected function getBannersData($bannersIds)
    {
        $banners = [];
        foreach ($bannersIds as $bannerId) {
            if (!isset($this->banners[$bannerId])) {
                $content = $this->bannerResource->getStoreContent($bannerId, $this->storeId);
                if (!empty($content)) {
                    $bannerLoaded = $this->banner->load($bannerId); //if remote banner, then do something add code or remove banner data if code is used.

                    if ($bannerLoaded->getDataByKey('is_remote') || $this->isEnabled()) {
                        $salesRuleIds = $this->bannerResource->getRelatedSalesRule($bannerId);
                        $salesRuleId = reset($salesRuleIds);
                        $this->banners[$bannerId]['is_remote'] = true;
                        $this->banners[$bannerId]['sales_ruleId'] = $salesRuleId;
                    }
                    $this->banners[$bannerId]['content'] = $this->filterProvider->getPageFilter()->filter($content);
                    $this->banners[$bannerId]['types'] = $bannerLoaded->getTypes();
                    $this->banners[$bannerId]['id'] = $bannerId;
                } else {
                    $this->banners[$bannerId] = null;
                }
            }
            $banners[$bannerId] = $this->banners[$bannerId];
        }
        return array_filter($banners);
    }

    private function isEnabled()
    {
        if (!$this->isEnabled ) {
            $this->isEnabled = $this->bannerHelper->isEnabled();
        }
        return $this->isEnabled;
    }

}
