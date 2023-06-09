<?php

namespace CJ\CustomAmastyReview\Model\Overridden;

use Amasty\AdvancedReview\Helper\Config;
use Amasty\AdvancedReview\Model\Email\CouponConditionsProvider;
use Amasty\AdvancedReview\Model\Email\CouponDataProvider;
use Amasty\AdvancedReview\Model\Email\Flag;
use Amasty\Base\Model\Serializer;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Converter\ToDataModel;
use Magento\SalesRule\Model\Converter\ToModel;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Condition\Address;
use Magento\SalesRule\Model\Rule\Condition\Combine as RuleConditionCombine;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine as RuleConditionProductCombine;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SalesRuleProvider
 */
class SalesRuleProvider extends \Amasty\AdvancedReview\Model\Email\SalesRuleProvider
{
    /**
     * @var string
     */
    public static $baseRuleName = 'Amasty Review Reminder Coupons';

    /**
     * @var int
     */
    public const STATUS_ACTIVE = 1;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CouponDataProvider
     */
    protected $couponDataProvider;

    /**
     * @var CouponConditionsProvider
     */
    protected $couponConditionsProvider;

    /**
     * @var ToDataModel
     */
    protected $toDataModelConverter;

    /**
     * @var ToModel
     */
    protected $toModelConverter;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var Flag
     */
    protected $flag;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var GroupCollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * @var RuleCollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @param RuleFactory $ruleFactory
     * @param RuleRepositoryInterface $ruleRepository
     * @param StoreManagerInterface $storeManager
     * @param CouponDataProvider $couponDataProvider
     * @param CouponConditionsProvider $couponConditionsProvider
     * @param ToDataModel $toDataModelConverter
     * @param ToModel $toModelConverter
     * @param Serializer $serializer
     * @param Flag $flag
     * @param DateTime $date
     * @param \CJ\CustomAmastyReview\Helper\Config $configHelper
     * @param GroupCollectionFactory $groupCollectionFactory
     * @param RuleCollectionFactory $ruleCollectionFactory
     */
    public function __construct(
        RuleFactory $ruleFactory,
        RuleRepositoryInterface $ruleRepository,
        StoreManagerInterface $storeManager,
        CouponDataProvider $couponDataProvider,
        CouponConditionsProvider $couponConditionsProvider,
        ToDataModel $toDataModelConverter,
        ToModel $toModelConverter,
        Serializer $serializer,
        Flag $flag,
        DateTime $date,
        \CJ\CustomAmastyReview\Helper\Config $configHelper,
        GroupCollectionFactory $groupCollectionFactory,
        RuleCollectionFactory $ruleCollectionFactory
    )
    {
        $this->configHelper = $configHelper;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->ruleCollectionFactory  = $ruleCollectionFactory;
        parent::__construct($ruleFactory, $ruleRepository, $storeManager, $couponDataProvider, $couponConditionsProvider, $toDataModelConverter, $toModelConverter, $serializer, $flag, $date);
        $this->ruleFactory = $ruleFactory;
        $this->ruleRepository = $ruleRepository;
        $this->storeManager = $storeManager;
        $this->couponDataProvider = $couponDataProvider;
        $this->couponConditionsProvider = $couponConditionsProvider;
        $this->toDataModelConverter = $toDataModelConverter;
        $this->toModelConverter = $toModelConverter;
        $this->serializer = $serializer;
        $this->flag = $flag;
        $this->date = $date;
    }

    /**
     * @param WebsiteInterface $website
     * @return Rule
     */
    public function getRule(WebsiteInterface $website): Rule
    {
        try {
            $rule = $this->ruleRepository->getById($this->flag->getRuleIdByWebsiteId((int)$website->getId()));
            $rule = $this->toModelConverter->toModel($rule);
            $now = $this->date->date('Y-m-d');

            if (!$rule->getIsActive() || !($rule->getFromDate() <= $now && $rule->getToDate() >= $now)) {
                $rule = $this->ruleFactory->create();
            }
        } catch (NoSuchEntityException $exception) {
            $rule = $this->ruleFactory->create();
        }

        $rule = $this->buildRule($rule, $website);
        $this->flag->addRuleIdByWebsite((int)$rule->getRuleId(), (int)$website->getId());

        return $this->toModelConverter->toModel($rule);
    }

    /**
     * Override get data follow store view
     *
     * @param Rule $rule
     * @param WebsiteInterface $website
     * @return RuleInterface
     */
    private function buildRule(Rule $rule, WebsiteInterface $website): RuleInterface
    {
        $storeId = $this->_getStoreIdByWebsite($website);
        $rule->loadPost(array_merge(
            $this->_generateCouponData($website, $storeId),
            $this->_generateConditions($storeId)
        ));
        $this->convertDateTimeIntoDates($rule);
        $rule->setConditionsSerialized($this->serializer->serialize($rule->getConditions()->asArray()));
        return $this->ruleRepository->save($this->toDataModelConverter->toDataModel($rule));
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
     * @param Rule $rule
     * @return void
     */
    private function convertDateTimeIntoDates(Rule $rule): void
    {
        foreach (['from_date', 'to_date'] as $ruleAttribute) {
            if ($ruleAttributeValue = $rule->getData($ruleAttribute)) {
                if ($ruleAttributeValue instanceof \DateTime) {
                    $ruleAttributeValue = $ruleAttributeValue->format('Y-m-d');
                    $rule->setData($ruleAttribute, $ruleAttributeValue);
                }
            }
        }
    }

    /**
     * @param $storeId
     * @return array[]
     */
    private function _generateConditions($storeId): array
    {
        return [
            'conditions' => [
                1 => [
                    'type'       => RuleConditionCombine::class,
                    'aggregator' => 'all',
                    'value'      => 1,
                    'new_child'  => '',
                    'conditions' => [
                        '1' => [
                            'type'      => Address::class,
                            'attribute' => 'base_subtotal',
                            'operator'  => '>=',
                            'value'     => (float) $this->configHelper->getModuleConfig('coupons/min_order', $storeId),
                        ]
                    ]
                ]
            ],
            'actions' => [
                1 => [
                    'type'       => RuleConditionProductCombine::class,
                    'aggregator' => 'all',
                    'value'      => 1,
                    'new_child'  => '',
                ]
            ]
        ];
    }

    /**
     * Override function from CouponDataProvider to change scope config
     *
     * @param WebsiteInterface $website
     * @param int $storeId
     * @return array
     */
    private function _generateCouponData(WebsiteInterface $website, $storeId): array
    {
        $collection = $this->ruleCollectionFactory->create()
            ->addFieldToFilter(
                'name',
                ['like' => sprintf('%s - %s', self::$baseRuleName, $website->getName()) . '%']
            );

        $days = (int) $this->configHelper->getModuleConfig('coupons/coupon_days', $storeId);
        $ruleName = sprintf(
            '%s - %s - %d',
            self::$baseRuleName,
            $website->getName(),
            $collection->getSize() + 1
        );

        return [
            'name'                  => $ruleName,
            'is_active'             => self::STATUS_ACTIVE,
            'coupon_type'           => Rule::COUPON_TYPE_SPECIFIC,
            'use_auto_generation'   => 1,
            'stop_rules_processing' => 0,
            'uses_per_coupon'       =>
                (int) $this->configHelper->getModuleConfig('coupons/coupon_uses', $storeId),
            'uses_per_customer'     => (int) $this->configHelper->getModuleConfig(
                'coupons/uses_per_customer',
                $storeId
            ),
            'from_date'             => $this->date->date('Y-m-d'),
            'to_date'               => $this->date->date('Y-m-d', strtotime("+$days days")),
            'simple_action'         =>
                $this->configHelper->getModuleConfig('coupons/discount_type', $storeId),
            'discount_amount'       =>
                $this->configHelper->getModuleConfig('coupons/discount_amount', $storeId),
            'website_ids'           => [$website->getId()],
            'customer_group_ids'    => $this->getCustomerGroupIds($storeId)
        ];
    }

    /**
     * Get customer group id from config
     *
     * @param int $storeId
     * @return array
     */
    private function getCustomerGroupIds($storeId): array
    {
        if (empty($customerGroupIds = $this->configHelper->getModuleConfig('coupons/customer_group', $storeId))) {
            $customerGroups = $this->groupCollectionFactory->create();
            foreach ($customerGroups as $group) {
                $customerGroupIds[] = $group->getId();
            }
        }

        return is_array($customerGroupIds) ? $customerGroupIds : explode(',', $customerGroupIds);
    }
}
