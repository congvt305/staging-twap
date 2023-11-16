<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\ResourceModel;

use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Model\ResourceModel\Sitemap\Actions\AdditionalActionsPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Sitemap extends AbstractDb
{
    public const TABLE_NAME = 'amasty_xmlsitemap';
    public const ENTITY_DATA_TABLE_NAME = 'amasty_xml_sitemap_entity_data';

    /**
     * @var AdditionalActionsPool
     */
    private $saveActionsPool;

    /**
     * @var AdditionalActionsPool
     */
    private $loadActionsPool;

    public function __construct(
        Context $context,
        AdditionalActionsPool $saveActionsPool,
        AdditionalActionsPool $loadActionsPool,
        $connectionName = null
    ) {
        $this->saveActionsPool = $saveActionsPool;
        $this->loadActionsPool = $loadActionsPool;

        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, SitemapInterface::SITEMAP_ID);
    }

    public function save(AbstractModel $object): void
    {
        parent::save($object);

        $additionalSaveActions = $this->saveActionsPool->getIterator();

        foreach ($additionalSaveActions as $action) {
            $action->execute([$object->getSitemapId() => $object]);
        }
    }

    public function _afterLoad($object): void
    {
        parent::_afterLoad($object);

        $additionalLoadActions = $this->loadActionsPool->getIterator();

        foreach ($additionalLoadActions as $action) {
            $action->execute([$object->getSitemapId() => $object]);
        }
    }
}
