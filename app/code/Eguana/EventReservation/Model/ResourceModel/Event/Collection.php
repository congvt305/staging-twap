<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 15/10/20
 * Time: 6:20 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Model\ResourceModel\Event;

use Eguana\EventReservation\Api\Data\EventInterface;
use Eguana\EventReservation\Model\Event as EventModel;
use Eguana\EventReservation\Model\ResourceModel\AbstractCollection;
use Eguana\EventReservation\Model\ResourceModel\Event as EventResourceModel;
use Magento\Store\Model\Store;

/**
 * collection for event model & resource model
 *
 * Event Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'event_id';

    /**
     * Load data for preview flag
     *
     * @var bool
     */
    protected $_previewFlag;

    /**
     * Event prefix
     *
     * @var string
     */
    protected $eventPrefix = 'event_reservation_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $eventObject = 'event_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            EventModel::class,
            EventResourceModel::class
        );
        $this->_map['fields']['store']      = 'store_table.store_id';
        $this->_map['fields']['event_id']   = 'main_table.event_id';
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
        return $this->_toOptionArray('event_id', 'title');
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
            $entityMetadata = $this->metadataPool->getMetadata(EventInterface::class);
            $this->performAfterLoad('eguana_event_reservation_store', $entityMetadata->getLinkField());
            $this->_previewFlag = false;
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }

        return parent::_afterLoad();
    }

    /**
     * Perform operations before rendering filters
     *
     * @return void
     */
    protected function _renderFiltersBefore() : void
    {
        try {
            $entityMetadata = $this->metadataPool->getMetadata(EventInterface::class);
            $this->joinStoreRelationTable(
                'eguana_event_reservation_store',
                $entityMetadata->getLinkField()
            );
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }
}
