<?php

namespace CJ\CustomAmastyReview\Model\Overridden;

use Amasty\AdvancedReview\Helper\Config;
use Amasty\AdvancedReview\Model\Email\CouponConditionsProvider;
use Amasty\AdvancedReview\Model\Email\CouponDataProvider;
use Amasty\AdvancedReview\Model\Email\Flag;
use Amasty\Base\Model\Serializer;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Converter\ToDataModel;
use Magento\SalesRule\Model\Converter\ToModel;
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
        \CJ\CustomAmastyReview\Helper\Config $configHelper
    )
    {
        $this->configHelper = $configHelper;
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
     * @param Rule $rule
     * @param WebsiteInterface $website
     * @return RuleInterface
     */
    private function buildRule(Rule $rule, WebsiteInterface $website): RuleInterface
    {
        $storeId = $this->_getStoreIdByWebsite($website);
        $rule->loadPost(array_merge(
            $this->couponDataProvider->generateCouponData($website),
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
    protected function _generateConditions($storeId): array
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
}
