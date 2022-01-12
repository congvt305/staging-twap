<?php

namespace CJ\LineShopping\Cron;

use CJ\LineShopping\Model\Export\CategoryAdapter;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Store\Model\StoreManagerInterface;
use CJ\LineShopping\Helper\Config;
use CJ\LineShopping\Model\FileSystem\FeedOutput;
use Exception;
use CJ\LineShopping\Logger\Logger;

class ExportFullCategory
{
    const TYPE_EXPORT = 'full_category';

    /**
     * @var CategoryFactory
     */
    protected CategoryFactory $categoryFactory;

    /**
     * @var CategoryAdapter
     */
    protected CategoryAdapter $categoryAdapter;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @var FeedOutput
     */
    protected FeedOutput $feedOutput;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @param Logger $logger
     * @param FeedOutput $feedOutput
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param CategoryAdapter $categoryAdapter
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        Logger $logger,
        FeedOutput $feedOutput,
        Config $config,
        StoreManagerInterface $storeManager,
        CategoryAdapter $categoryAdapter,
        CategoryFactory $categoryFactory
    ) {
        $this->logger = $logger;
        $this->feedOutput = $feedOutput;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->categoryAdapter = $categoryAdapter;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * Execute export full category
     */
    public function execute()
    {
        foreach ($this->storeManager->getWebsites() as $website) {
            if ($this->config->isEnable($website->getId())) {
                try {
                    $parentId = $this->storeManager->getStore()->getRootCategoryId();
                    $categories = $this->categoryFactory->create()->getCategories($parentId, 0, false, true);
                    $listCategory = $this->categoryAdapter->export($categories, $parentId);
                    $this->feedOutput->createJsonFile(self::TYPE_EXPORT, $website->getId(), $listCategory);
                } catch (Exception $exception) {
                    $this->logger->addError(Logger::EXPORT_FEED_DATA,
                        [
                            'type' => self::TYPE_EXPORT,
                            'message' => $exception->getMessage()
                        ]
                    );
                    continue;
                }
            }
        }
    }
}
