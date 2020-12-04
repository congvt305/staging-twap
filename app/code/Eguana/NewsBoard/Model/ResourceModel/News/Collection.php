<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 29/6/20
 * Time: 4:10 PM
 */
declare(strict_types=1);

namespace Eguana\NewsBoard\Model\ResourceModel\News;

use Eguana\NewsBoard\Api\Data\NewsInterface;
use Eguana\NewsBoard\Model\News as NewsModel;
use Eguana\NewsBoard\Model\ResourceModel\AbstractCollection;
use Eguana\NewsBoard\Model\ResourceModel\News as NewsResourceModel;
use Magento\Store\Model\Store;

/**
 * collection for news model & resource model
 *
 * News Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'news_id';

    /**
     * Load data for preview flag
     *
     * @var bool
     */
    protected $_previewFlag;

    /**
     * News prefix
     *
     * @var string
     */
    protected $eventPrefix = 'news_board_collection';

    /**
     * News object
     *
     * @var string
     */
    protected $eventObject = 'news_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            NewsModel::class,
            NewsResourceModel::class
        );
        $this->_map['fields']['store']      = 'store_table.store_id';
        $this->_map['fields']['news_id']   = 'main_table.news_id';
    }

    /**
     * Set first store flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setFirstStoreFlag($flag = false)
    {
        $this->_previewFlag = $flag;
        return $this;
    }

    /**
     * Add filter by store
     *
     * @param int|array|Store $store
     * @param bool $withAdmin
     * @return $this|mixed
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
            $this->setFlag('store_filter_added', true);
        }

        return $this;
    }

    /**
     * Options array method
     *
     * @return array
     */
    public function toOptionArray() : array
    {
        return $this->_toOptionArray('news_id', 'title');
    }

    /**
     * Retrieve all ids for collection
     * Backward compatibility with EAV collection
     *
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null) : array
    {
        return $this->getConnection()->fetchCol(
            $this->_getAllIdsSelect($limit, $offset),
            $this->_bindParams
        );
    }

    /**
     * Perform operations after collection load
     *
     * @return Collection
     */
    protected function _afterLoad() : Collection
    {
        try {
            $entityMetadata = $this->metadataPool->getMetadata(NewsInterface::class);
            $this->performAfterLoad('eguana_news_store', $entityMetadata->getLinkField());
            $this->_previewFlag = false;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return parent::_afterLoad();
    }

    /**
     * Perform operations before rendering filters
     *
     * @throws \Exception
     */
    protected function _renderFiltersBefore() : void
    {
        try {
            $entityMetadata = $this->metadataPool->getMetadata(NewsInterface::class);
            $this->joinStoreRelationTable(
                'eguana_news_store',
                $entityMetadata->getLinkField()
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
