<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\ResourceModel\Sitemap;

use Amasty\XmlSitemap\Model\ResourceModel\Sitemap as SitemapResource;
use Amasty\XmlSitemap\Model\ResourceModel\Sitemap\Actions\AdditionalActionsPool;
use Amasty\XmlSitemap\Model\Sitemap as SitemapModel;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    /**
     * @var AdditionalActionsPool
     */
    private $loadActionsPool;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        AdditionalActionsPool $loadActionsPool,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->loadActionsPool = $loadActionsPool;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    protected function _construct(): void
    {
        $this->_init(SitemapModel::class, SitemapResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
        $this->addFilterToMap(SitemapModel::SITEMAP_ID, sprintf('main_table.%s', SitemapModel::SITEMAP_ID));
    }

    public function addStoreFilter(array $storeIds): void
    {
        $this->addFieldToFilter(Store::STORE_ID, $storeIds);
    }

    public function _afterLoadData(): void
    {
        parent::_afterLoadData();

        $additionalLoadActions = $this->loadActionsPool->getIterator();

        foreach ($additionalLoadActions as $action) {
            $action->execute($this->getItems());
        }
    }
}
