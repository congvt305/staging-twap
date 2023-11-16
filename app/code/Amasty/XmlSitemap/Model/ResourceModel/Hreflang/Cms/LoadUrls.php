<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\ResourceModel\Hreflang\Cms;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\Framework\EntityManager\MetadataPool;

class LoadUrls
{
    /**
     * @var PageResource
     */
    private $pageResource;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    public function __construct(PageResource $pageResource, MetadataPool $metadataPool)
    {
        $this->pageResource = $pageResource;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @return array [['id' => 1, 'store_id' => 1, 'request_path' => ''], ...]
     */
    public function execute(array $pageIds, array $storeIds, string $idFieldName): array
    {
        $linkField = $this->getLinkField();

        $select = $this->pageResource->getConnection()->select()->from(
            ['main_table' => $this->pageResource->getMainTable()],
            ['id' => $idFieldName, 'request_path' => PageInterface::IDENTIFIER]
        )->join(
            ['page_store' => $this->pageResource->getTable('cms_page_store')],
            sprintf('main_table.%1$s = page_store.%1$s', $linkField),
            ['store_id']
        )->where(
            sprintf('main_table.%s != ""', $idFieldName)
        )->where(
            'store_id IN (?)',
            $storeIds
        )->where(
            sprintf('main_table.%s IN (?)', $linkField),
            $pageIds
        )->where(
            sprintf('%s = ?', PageInterface::IS_ACTIVE),
            1
        );

        return $this->pageResource->getConnection()->fetchAll($select);
    }

    private function getLinkField(): string
    {
        return $this->metadataPool->getMetadata(PageInterface::class)->getLinkField();
    }
}
