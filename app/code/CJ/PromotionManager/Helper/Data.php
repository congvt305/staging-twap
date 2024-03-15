<?php

namespace CJ\PromotionManager\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

use \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use \Magento\CatalogRule\Api\Data\RuleInterfaceFactory as RuleFactory;
use \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface as CatalogRuleRepository;

class Data extends AbstractHelper
{
    /** @var \Magento\Framework\App\State **/
    private $state;
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var RuleCollectionFactory
     */
    protected $ruleCollectionFactory;
    /**
     * @var CatalogRuleRepository
     */
    protected $catalogRuleRepository;
    /**
     * @var RuleFactory
     */
    protected $ruleFactory;
    /**
     * @var PsrLoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param CatalogRuleRepository $catalogRuleRepository
     * @param RuleFactory $ruleFactory
     * @param RuleCollectionFactory $rule
     * @param PsrLoggerInterface $logger
     */
    public function __construct(
        Context $context,
        CatalogRuleRepository $catalogRuleRepository,
        RuleFactory $ruleFactory,
        PsrLoggerInterface $logger,
        \Magento\Framework\App\State $state,
        RuleCollectionFactory $ruleCollectionFactory
    ){
        parent::__construct($context);
        $this->catalogRuleRepository = $catalogRuleRepository;
        $this->ruleFactory = $ruleFactory;
        $this->logger = $logger;
        $this->state = $state;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
    }

    /**
     * @param $fromStoreId
     * @param $toWebsiteId
     * @return void
     */
    public function migratePromotions($fromWebsiteId, $toWebsiteId)
    {
        $this->migrateCatalogRule($fromWebsiteId, $toWebsiteId);
        $this->migrateCartRule($fromWebsiteId, $toWebsiteId);
    }

    /**
     * @param $fromWebsiteId
     * @param $toWebsiteId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function migrateCatalogRule($fromWebsiteId, $toWebsiteId)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->logger->info('============ Migration Promotion ==============');

        $items = $objectManager->create('\Magento\CatalogRule\Model\RuleFactory')
            ->create()
            ->getCollection()
            ->addWebsiteFilter($fromWebsiteId)
            ->addIsActiveFilter(1);

        $this->logger->info(__('Number Promotion clone %1', $items->getSize()));
        $tmp = 0;

        if (!empty($items)) {
            foreach ($items as $item) {
                /**
                 * @var \Magento\CatalogRule\Model\Rule $item
                 */
                $ruleData = [
                    'name' => $item->getName(),
                    'is_active' => $item->getIsActive(),
                    'customer_group_ids' => $item->getCustomerGroupIds(),
                    'discount_amount'=> $item->getDiscountAmount(),
                    'simple_action' => $item->getSimpleAction(),
                    'conditions' => $item->getConditions(),
                    'from_date' => $item->getFromDate(),
                    'to_date' => $item->getToDate(),
                    'website_ids' => [$toWebsiteId]
                ];
                try {
                    $catalogRule = $this->createRuleFactory();
                    $catalogRule->loadPost($ruleData);
                    $this->catalogRuleRepository->save($catalogRule);
                    $tmp++;
                } catch (\Exception $exception) {
                    $this->logger->info(__('ID Promotion Clone Fail: %1', $item->getRuleId()));
                    $this->logger->info(__('Error Message : %1', $exception->getMessage()));
                    continue;
                }
            }
            $this->logger->info(__('Number Promotion clone completed %1', $tmp));
        }
        $this->logger->info(__('Empty Promotion'));
    }

    public function createRuleFactory()
    {
        return $this->ruleFactory->create();
    }
    public function migrateCartRule($fromWebsiteId, $toWebsiteId)
    {
        $this->logger->info('============ Migration Cart Rule ==============');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $items = $objectManager->create('\Magento\CatalogRule\Model\RuleFactory')
            ->create()
            ->getCollection()
            ->addWebsiteFilter($fromWebsiteId)
            ->addIsActiveFilter(1);
        $this->logger->info(__('Number Cart rule clone %1', $items->getSize()));

        $tmpCartRule = 0;

        foreach( $items as $item){
            /**
             * @var \Magento\SalesRule\Model\Rule $item
             */
            $salesRule = $objectManager->create('Magento\SalesRule\Model\Rule');
            $salesRule->setName( $item->getName() )
                ->setDescription( $item->getDescription())
                ->setFromDate( $item->getFromDate())
                ->setToDate( $item->getToDate())
                ->setUsesPerCustomer( $item->getUsesPerCustomer() )
                ->setCustomerGroupIds( $item->getCustomerGroupIds() )
                ->setIsActive( $item->getIsActive())
                ->setStopRulesProcessing( $item->getStopRulesProcessing() )
                ->setIsAdvanced( $item->getIsAdvanced() )
                ->setProductIds( $item->getProductIds())
                ->setSortOrder( $item->getSortOrder() )
                ->setSimpleAction( $item->getSimpleAction() )
                ->setDiscountAmount( $item->getDiscountAmount())
                ->setDiscountQty( $item->getDiscountQty() )
                ->setDiscountStep( $item->getDiscountStep() )
                ->setApplyToShipping( $item->getApplyToShipping() )
                ->setTimesUsed( $item->getTimesUsed() )
                ->setIsRss( $item->getIsRss() )
                ->setWebsiteIds( [$toWebsiteId] )
                ->setCouponType( $item->getCouponType() )
                ->setCouponCode( $item->getCouponCode() )
                ->setUsesPerCoupon( $item->getUsesPerCoupon() );

            try {
                $salesRule->save();
                $tmpCartRule++;
            } catch (Exception $exception) {
                $this->logger->info(__('ID Promotion Clone Fail: %1', $item->getRuleId()));
                $this->logger->info(__('Error Message : %1', $exception->getMessage()));
            }
        }
        $this->logger->info(__('Number Cart clone completed %1', $tmpCartRule));
        $this->logger->info('============ Migration Cart Rule Complete ==============');
    }

}
