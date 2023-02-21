<?php

namespace CJ\CustomAmastyReview\Model\Overridden;

use Amasty\AdvancedReview\Helper\Config;
use Amasty\AdvancedReview\Model\Email\CouponDataProvider;
use Amasty\AdvancedReview\Model\Email\SalesRuleProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class EmailCoupon
 */
class EmailCoupon extends \Amasty\AdvancedReview\Model\Email\Coupon
{
    /**
     * @var bool
     */
    protected $sendCoupon = false;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var CollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CouponFactory
     */
    protected $couponFactory;

    /**
     * @var CouponRepositoryInterface
     */
    protected $couponRepository;

    /**
     * @var SalesRuleProvider
     */
    protected $salesRuleProvider;

    /**
     * @var \CJ\CustomAmastyReview\Helper\Config
     */
    protected $helper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Config $configHelper
     * @param DateTime $date
     * @param CollectionFactory $ruleCollectionFactory
     * @param LoggerInterface $logger
     * @param RuleRepositoryInterface $ruleRepository
     * @param CouponFactory $couponFactory
     * @param CouponRepositoryInterface $couponRepository
     * @param SalesRuleProvider $salesRuleProvider
     * @param \CJ\CustomAmastyReview\Helper\Config $helper
     */
    public function __construct(
        Config $configHelper,
        DateTime $date,
        CollectionFactory $ruleCollectionFactory,
        LoggerInterface $logger,
        RuleRepositoryInterface $ruleRepository,
        CouponFactory $couponFactory,
        CouponRepositoryInterface $couponRepository,
        SalesRuleProvider $salesRuleProvider,
        \CJ\CustomAmastyReview\Helper\Config $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        parent::__construct($configHelper, $date, $ruleCollectionFactory, $logger, $ruleRepository, $couponFactory, $couponRepository, $salesRuleProvider);
        $this->date = $date;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->logger = $logger;
        $this->ruleRepository = $ruleRepository;
        $this->couponFactory = $couponFactory;
        $this->couponRepository = $couponRepository;
        $this->salesRuleProvider = $salesRuleProvider;
    }

    /**
     * @param   string $couponCode
     * @param   int $days
     * @return  string
     */
    private function getCouponText(string $couponCode, int $days): string
    {
        $daysMessage = $this->getDaysMessage($days);
        return sprintf(
            '<p class="amcomment">%s</p><p class="am-coupon-container">%s<span class="coupon">%s</span> (%s).</p>',
            __('It will take only a few minutes, just click the \'Leave a review\' button below.'),
            __(
                'To make the process more pleasant we are happy to grant you a discount coupon code,'
                . ' which can already be used for your next purchase. Here it is: '
            ),
            $couponCode,
            $daysMessage
        );
    }

    /**
     * @return string
     */
    private function getNoCouponText(): string
    {
        return (string) __('It will take only a few minutes, just click the \'Leave a review\' button below.');
    }

    /**
     * @return Collection
     */
    private function getExpiredRuleCollection(): Collection
    {
        /** @var Collection $collection */
        $collection = $this->ruleCollectionFactory->create();
        $collection->addFieldToFilter('coupon_type', ['eq' => Rule::COUPON_TYPE_SPECIFIC]);
        $collection->addFieldToFilter(
            ['name', 'name'],
            [['like' => '%@%.%'], ['like' => CouponDataProvider::$baseRuleName . ' %']]
        );
        $collection->addFieldToFilter('to_date', ['lt' => $this->date->date('Y-m-d')]);
        return $collection;
    }

    /**
     *
     * @return void
     * @throws LocalizedException
     */
    public function removeOldCoupons(): void
    {
        /** @var Rule $rule */
        foreach ($this->getExpiredRuleCollection() as $rule) {
            try {
                $this->ruleRepository->deleteById($rule->getRuleId());
            } catch (\Exception $e) {
                throw new LocalizedException(
                    __("\r\nError when deleting rule #%s : %s", $rule->getId(), $e->getMessage())
                );
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getCouponMessage(WebsiteInterface $website): string
    {
        $storeId = $this->_getStoreIdByWebsite($website);
        if ($this->helper->isAllowCoupons($storeId)) {
            if ($this->helper->isNeedReview($storeId)) {
                $message = $this->getReviewText();
            } else {
                $days = (int)$this->helper->getModuleConfig('coupons/coupon_days', $storeId);
                $couponCode = $this->generateCoupon($website);
                $message = $this->getCouponText($couponCode, $days);

                if ($couponCode) {
                    $this->sendCoupon = true;
                }
            }
        } else {
            $message = $this->getNoCouponText();
        }

        return $message;
    }

    /**
     * @param WebsiteInterface $website
     * @return int
     */
    private function _getStoreIdByWebsite(WebsiteInterface $website): int
    {
        $storeId = null;
        foreach ($this->storeManager->getStores() as $store) {
            if ($website->getId() === $store->getWebsiteId()) {
                $storeId = $store->getId();
            }
        }
        return $storeId;
    }

    /**
     * Move initialize coupon out of try catch
     *
     * @param WebsiteInterface $website
     * @return string
     */
    public function generateCoupon(WebsiteInterface $website): string
    {
        $coupon = $this->couponFactory->create();
        try {
            $rule = $this->salesRuleProvider->getRule($website);

            $store = $website->getDefaultStore();
            $coupon->setId(null)
                ->setRuleId($rule->getId())
                ->setUsageLimit((int)$this->configHelper->getModuleConfig('coupons/coupon_uses', $store))
                ->setUsagePerCustomer(
                    (int)$this->configHelper->getModuleConfig('coupons/uses_per_customer', $store)
                )
                ->setCreatedAt($this->date->date())
                ->setType(\Magento\SalesRule\Helper\Coupon::COUPON_TYPE_SPECIFIC_AUTOGENERATED)
                ->setCode($rule->getCouponCodeGenerator()->generateCode());

            $this->couponRepository->save($coupon);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $coupon->getCode() ?? '';
    }

}
