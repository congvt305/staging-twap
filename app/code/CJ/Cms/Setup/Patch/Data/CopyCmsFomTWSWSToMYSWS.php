<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CJ\Cms\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollection;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollection;
use Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory as WidgetCollection;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Widget\Model\Widget\InstanceFactory;
use Magento\Theme\Model\View\Design;
use Magento\Framework\Locale\ResolverInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

class CopyCmsFomTWSWSToMYSWS implements DataPatchInterface, PatchVersionInterface
{
    const TW_SWS_STORE_CODE = 'default';
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    private $pageFactory;
    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    private $blockFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var PageCollection
     */
    protected $pageCollection;
    /**
     * @var BlockCollection
     */
    protected $blockCollection;
    /**
     * @var WidgetCollection
     */
    protected $widgetCollection;
    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var InstanceFactory
     */
    protected $instanceFactory;
    /**
     * @var Design
     */
    protected $design;
    /**
     * @var ResolverInterface
     */
    protected $resolver;
    /**
     * @var PsrLoggerInterface
     */
    protected $logger;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param PageCollection $pageCollection
     * @param BlockCollection $blockCollection
     * @param WidgetCollection $widgetCollection
     * @param StoreRepositoryInterface $storeRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param InstanceFactory $instanceFactory
     * @param Design $design
     * @param ResolverInterface $resolver
     * @param PsrLoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        PageCollection $pageCollection,
        BlockCollection $blockCollection,
        WidgetCollection $widgetCollection,
        StoreRepositoryInterface $storeRepository,
        ScopeConfigInterface $scopeConfig,
        InstanceFactory $instanceFactory,
        Design $design,
        ResolverInterface $resolver,
        PsrLoggerInterface $logger
    ) {
        $this->pageFactory = $pageFactory;
        $this->blockFactory = $blockFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->pageCollection = $pageCollection;
        $this->blockCollection = $blockCollection;
        $this->widgetCollection = $widgetCollection;
        $this->storeRepository = $storeRepository;
        $this->scopeConfig = $scopeConfig;
        $this->instanceFactory = $instanceFactory;
        $this->design = $design;
        $this->resolver = $resolver;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $storeTWSWSId = $this->storeRepository->get(self::TW_SWS_STORE_CODE)->getId();
        $storeMYSWSId = $this->storeRepository->get(self::MY_SWS_STORE_CODE)->getId();
        if ($storeTWSWSId && $storeMYSWSId){
            $this->migrateCmsPage($storeTWSWSId, $storeMYSWSId);
            $this->migrateCmsBlock($storeTWSWSId, $storeMYSWSId);
            $this->migrateWidgets($storeTWSWSId, $storeMYSWSId);
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Create page model instance
     *
     * @return \Magento\Cms\Model\Page
     */
    private function createPage()
    {
        return $this->pageFactory->create();
    }

    /**
     * Migrade data cms page from VN LNG to MY LNG
     * @param $storeVNLNGId
     * @param $storeMYLNGId
     * @return void
     */
    private function migrateCmsPage($storeVNLNGId, $storeMYLNGId)
    {
        $this->logger->info('============ Migration Pages ==============');
        $cmsPages = $this->pageCollection->create()->addStoreFilter((int)$storeVNLNGId, false);
        $this->logger->info(__('Number pages clone %1', $cmsPages->getSize()));
        /**
         * Clone cms page from VN LNG to MY LNG
         */
        $tmp = 0;
        foreach ($cmsPages as $item) {
            try {
                /**
                 *  @var \Magento\Cms\Model\Page $item
                 */
                $data = [
                    'title' => $item->getTitle(),
                    'page_layout' => $item->getPageLayout(),
                    'meta_keywords' => $item->getMetaKeywords(),
                    'meta_description' => $item->getMetaDescription(),
                    'identifier' => $item->getIdentifier(),
                    'content_heading' => $item->getContentHeading(),
                    'content' => $item->getContent(),
                    'is_active' => 1,
                    'stores' => [$storeMYLNGId],
                    'sort_order' => 0
                ];
                $this->createPage()->setData($data)->save();
                $tmp++;
            }catch (\Exception $exception){
                $this->logger->info(__('ID Page Clone Fail: %1', $item->getId()));
                continue;
            }
        }
        $this->logger->info(__('Number Pages clone completed %1', $tmp));
    }
    /**
     * Migrade data cms block from VN LNG to MY LNG
     * @param $storeVNLNGId
     * @param $storeMYLNGId
     * @return void
     */
    private function migrateCmsBlock($storeVNLNGId, $storeMYLNGId)
    {
        $this->logger->info('============ Migration Blocks ==============');
        $cmsBlocks = $this->blockCollection->create()->addStoreFilter((int)$storeVNLNGId, false);
        $this->logger->info(__('Number blocks clone %1', $cmsBlocks->getSize()));
        /**
         * Clone cms block from VN LNG to MY LNG
         */
        $tmp = 0;
        foreach ($cmsBlocks as $item) {
            try {
                /**
                 *  @var \Magento\Cms\Model\Block $item
                 */
                $data = [
                    'title' => $item->getTitle(),
                    'identifier' => $item->getIdentifier(),
                    'content' => $item->getContent(),
                    'is_active' => 1,
                    'stores' => [$storeMYLNGId]
                ];
                $this->createBlock()->setData($data)->save();
                $tmp++;
            }catch (\Exception $exception){
                $this->logger->info(__('ID Page Clone Fail: %1', $item->getId()));
                continue;
            }
        }
        $this->logger->info(__('Number Blocks clone completed %1', $tmp));
    }
    /**
     * Create block model instance
     *
     * @return \Magento\Cms\Model\Block
     */
    private function createBlock()
    {
        return $this->blockFactory->create();
    }

    /**
     * @param $storeVNLNGId
     * @param $storeMYLNGId
     * @return void
     */
    private function migrateWidgets($storeVNLNGId, $storeMYLNGId)
    {
        $this->logger->info('============ Migration Widgets ==============');
        $widgets = $this->widgetCollection->create()->addStoreFilter((int)$storeVNLNGId, false);
        $this->logger->info(__('Number widgets clone %1', $widgets->getSize()));
        $themeId = $this->scopeConfig->getValue(
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeMYLNGId
        );
        $this->design->setArea('adminhtml');
        $this->design->setDesignTheme($this->design->getConfigurationDesignTheme());
        $locale = $this->resolver->setLocale('en_US')->setDefaultLocale('en_US');
        $this->design->setLocale($locale);
        if ($themeId){
            /**
             * Clone widget from TW SWS to MY SWS
             */
            $tmp = 0;
            foreach ($widgets as $item) {
                try {
                    /**
                     *  @var \Magento\Widget\Model\Widget\Instance $item
                     */
                    $data = [
                        'instance_type' => $item->getType(),
                        'theme_id' => $themeId,
                        'title' => $item->getTitle(),
                        'store_ids' => [$storeMYLNGId],
                        'widget_parameters' => $item->getWidgetParameters(),
                        'instance_code' => $item->getCode(),
                        'sort_order' => $item->getSortOrder()
                    ];

                    foreach($item->load($item->getId())->getData('page_groups') as $group) {
                        $page_group_name = $group['page_group']; // i.e. 'anchor_categories', 'simple_products'
                        $new_group = [
                                        'page_group' => $page_group_name,
                                        $page_group_name => [
                                            'page_id' => "0",
                                            'layout_handle' => $group['layout_handle'],
                                            'entities' => $group['entities'],
                                            'for' => $group['page_for'],
                                            'block' => $group['block_reference'],
                                            'template' => $group['page_template'],
                                        ]
                                    ];

                        $data['page_groups'][] = $new_group;
                    }
                    $this->createWidgetInstance()->setData($data)->save();
                    $tmp++;
                }catch (\Exception $exception){
                    $this->logger->info(__('ID Widget Clone Fail: %1', $item->getId()));
                    continue;
                }
            }
            $this->logger->info(__('Number widgets clone completed %1', $tmp));
        }
    }

    /**
     * @return \Magento\Widget\Model\Widget\Instance
     */
    private function createWidgetInstance()
    {
        return $this->instanceFactory->create();
    }
}
