<?php

namespace CJ\CustomAmastyReview\Model\Overridden;

use Amasty\AdvancedReview\Helper\Config;
use Amasty\AdvancedReview\Model\Email\CouponDataProvider;
use Amasty\AdvancedReview\Model\Email\SalesRuleProvider;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Api\Data\WebsiteInterface;
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
        \CJ\CustomAmastyReview\Helper\Config $helper
    ) {
        $this->helper = $helper;
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
     * {@inheritDoc}
     */
    public function getCouponMessage(WebsiteInterface $website): string
    {
        if ($this->helper->isAllowCoupons($website->getId())) {
            if ($this->helper->isNeedReview($website->getId())) {
                $message = $this->getReviewText();
            } else {
                $days = (int)$this->helper->getModuleConfig('coupons/coupon_days', $website->getId());
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
}
