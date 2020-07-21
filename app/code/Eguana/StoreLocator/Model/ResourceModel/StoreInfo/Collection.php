<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 06/17/20
 * Time: 12:00 PM
 */
namespace Eguana\StoreLocator\Model\ResourceModel\StoreInfo;

use Eguana\StoreLocator\Api\Data\StoreInfoInterface;
use Eguana\StoreLocator\Model\ResourceModel\AbstractCollection;
use Eguana\StoreLocator\Model\ResourceModel\StoreInfo as ResourceModel;
use Eguana\StoreLocator\Model\StoreInfo as Model;
use Magento\Framework\DB\Select as SelectAlias;
use Magento\Framework\Exception\NoSuchEntityException as NoSuchEntityExceptionAlias;
use Magento\Store\Model\Store as StoreAlias;

/**
 * Collection class for store info
 *
 * Class Collection
 *  Eguana\StoreLocator\Model\ResourceModel\StoreInfo
 */
class Collection extends AbstractCollection
{
    /**
     * @var
     */
    protected $aggregations;

    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * constructor
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
        $this->_map['fields']['store'] = 'store_table.store_id';
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }

    /**
     * Add filters after load
     * @return Collection
     * @throws NoSuchEntityExceptionAlias
     */
    protected function _afterLoad()
    {
        $entityMetadata = $this->metadataPool->getMetadata(StoreInfoInterface::class);
        $this->performAfterLoad('eguana_storelocator_store', $entityMetadata->getLinkField());
        return parent::_afterLoad();
    }

    /**
     * Add store filter on collection
     * @param array|int|StoreAlias $store
     * @param bool $withAdmin
     * @return $this|Collection
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
     * This function will return collection of store under the radius
     * @param $currentLocationPoint
     * @param $radius
     */
    public function addDistance($currentLocationPoint, $radius)
    {
        if (!empty($currentLocationPoint)) {
            $select = $this->getSelect();
            $this->addExpressionFieldToSelect(
                'distance',
                '(111.111 *
            DEGREES(ACOS(LEAST(1.0, COS(RADIANS(SUBSTRING_INDEX({{location}},",",1)))
                * COS(RADIANS({{latitude}}))
                * COS(RADIANS(SUBSTRING_INDEX({{location}},",",-1) - {{longitude}}))
                + SIN(RADIANS(SUBSTRING_INDEX({{location}},",",1)))
                * SIN(RADIANS({{latitude}}))))))',
                [
                    'location'=> 'location',
                    'latitude'=>$currentLocationPoint['lat'],
                    'longitude'=>$currentLocationPoint['long']
                ]
            );
        }
    }

    /**
     * Get select count
     * @return SelectAlias
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $select = clone $this->getSelect();
        $select->reset(SelectAlias::ORDER);
        $select->reset(SelectAlias::LIMIT_COUNT);
        $select->reset(SelectAlias::LIMIT_OFFSET);

        $countSelect = $this->_conn->select();
        $countSelect->from(['s' => $select]);
        $countSelect->reset(SelectAlias::COLUMNS);
        $countSelect->columns(new \Zend_Db_Expr('COUNT(*)'));
        return $countSelect;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $entityMetadata = $this->metadataPool->getMetadata(StoreInfoInterface::class);
        $this->joinStoreRelationTable('eguana_storelocator_store', $entityMetadata->getLinkField());
    }
}
