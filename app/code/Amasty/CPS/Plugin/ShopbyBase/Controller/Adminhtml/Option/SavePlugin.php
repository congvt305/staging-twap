<?php

declare(strict_types=1);

namespace Amasty\CPS\Plugin\ShopbyBase\Controller\Adminhtml\Option;

use Amasty\CPS\Model\BrandProduct;
use Amasty\CPS\Model\Product\AdminhtmlDataProvider;
use Amasty\ShopbyBase\Controller\Adminhtml\Option\Save;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\Indexer\IndexerRegistry;

class SavePlugin
{
    /**
     * @var AdminhtmlDataProvider
     */
    private $dataProvider;

    /**
     * @var BrandProduct
     */
    private $brandProduct;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    public function __construct(
        AdminhtmlDataProvider $dataProvider,
        BrandProduct $brandProduct,
        IndexerRegistry $indexerRegistry
    ) {
        $this->dataProvider = $dataProvider;
        $this->brandProduct = $brandProduct;
        $this->indexerRegistry = $indexerRegistry;
    }

    public function beforeExecute(Save $subject): array
    {
        $subject->getRequest()->setPostValue('sorting', $this->dataProvider->getSortOrder());
        $this->brandProduct->pinProduct(
            $this->dataProvider->getBrandId(),
            $this->dataProvider->getStoreId(),
            $this->dataProvider->getProductPositionData()
        );
        $indexer = $this->indexerRegistry->get(Fulltext::INDEXER_ID);

        if (!$indexer->isScheduled()) {
            $indexer->reindexList(array_flip($this->dataProvider->getProductIds()));
        }

        return [];
    }
}
