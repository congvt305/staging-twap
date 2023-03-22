<?php

namespace CJ\LineShopping\Cron;

use CJ\LineShopping\Logger\Logger;
use CJ\LineShopping\Model\Export\ProductAdapter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use CJ\LineShopping\Helper\Config;
use CJ\LineShopping\Model\FileSystem\FeedOutput;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Exception;

class ExportPartialProduct
{
    const TYPE_EXPORT = 'partial_product';

    /**
     * @var ProductCollectionFactory
     */
    protected ProductCollectionFactory $productCollectionFactory;

    /**
     * @var ProductAdapter
     */
    protected ProductAdapter $productAdapter;

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
     * @var ProductAction
     */
    protected ProductAction $productAction;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @param Logger $logger
     * @param ProductAction $productAction
     * @param FeedOutput $feedOutput
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param ProductAdapter $productAdapter
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        Logger $logger,
        ProductAction $productAction,
        FeedOutput $feedOutput,
        Config $config,
        StoreManagerInterface $storeManager,
        ProductAdapter $productAdapter,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->logger = $logger;
        $this->productAction = $productAction;
        $this->feedOutput = $feedOutput;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->productAdapter = $productAdapter;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Execute export partial product
     */
    public function execute()
    {
        foreach ($this->storeManager->getWebsites() as $website) {
            if ($this->config->isEnable($website->getId())) {
                try {
                    $products = $this->productCollectionFactory->create()
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                        ->addAttributeToFilter('line_sync_status', true)
                        ->addStoreFilter($website->getDefaultStore()->getId());
                    $listProduct = $this->productAdapter->export($products, $website);
                    $this->feedOutput->createJsonFile(self::TYPE_EXPORT, $website->getId(), $listProduct);
                    $this->updateModifyProduct($products, $website->getDefaultStore()->getId());
                } catch (Exception $exception) {
                    $this->logger->error(Logger::EXPORT_FEED_DATA,
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

    /**
     * @param $products
     * @param $storeId
     * @return void
     */
    public function updateModifyProduct($products, $storeId)
    {
        try {
            $ids = [];
            $i = 0;
            foreach ($products as $product) {
                $ids[$i] = $product->getEntityId();
                $i++;
            }
            $this->productAction->updateAttributes($ids, array('line_sync_status' => false), $storeId);
        } catch (Exception $exception) {
            $this->logger->error(Logger::EXPORT_FEED_DATA,
                [
                    'type' => self::TYPE_EXPORT,
                    'message' => $exception->getMessage()
                ]
            );
        }
    }
}
