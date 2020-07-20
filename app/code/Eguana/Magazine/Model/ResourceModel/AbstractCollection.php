<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/16/20
 * Time: 1:07 AM
 */
namespace Eguana\Magazine\Model\ResourceModel;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategyInterfaceAlias;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactoryInterfaceAlias;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\AdapterInterface as AdapterInterfaceAlias;
use Magento\Framework\DB\Select as SelectAlias;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\MetadataPool as MetadataPoolAlias;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\ManagerInterface as ManagerInterfaceAlias;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb as AbstractDbAlias;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as AbstractCollectionAlias;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterfaceAlias;
use Psr\Log\LoggerInterface;

/**
 * Abstract collection of Eguana Magazine
 */
abstract class AbstractCollection extends AbstractCollectionAlias
{
    /**
     * Store manager
     *
     * @var StoreManagerInterfaceAlias
     */
    protected $storeManager;

    /**
     * @var MetadataPoolAlias
     */
    protected $metadataPool;

    /**
     * @param EntityFactoryInterfaceAlias $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param FetchStrategyInterfaceAlias $fetchStrategy
     * @param ManagerInterfaceAlias $eventManager
     * @param StoreManagerInterfaceAlias $storeManager
     * @param MetadataPoolAlias $metadataPool
     * @param AdapterInterfaceAlias|null $connection
     * @param AbstractDbAlias|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * Perform operations after collection load
     * @param $tableName
     * @param $linkField
     */
    protected function performAfterLoad($tableName, $linkField)
    {
        try {
            $linkedIds = $this->getColumnValues($linkField);
            if (count($linkedIds)) {
                $connection = $this->getConnection();
                $select = $connection->select()->from(['eguana_magazine_store' => $this->getTable($tableName)])
                    ->where('eguana_magazine_store.' . $linkField . ' IN (?)', $linkedIds);
                $result = $connection->fetchAll($select);
                if ($result) {
                    $storesData = [];
                    foreach ($result as $storeData) {
                        $storesData[$storeData[$linkField]][] = $storeData['store_id'];
                    }

                    foreach ($this as $item) {
                        $linkedId = $item->getData($linkField);
                        if (!isset($storesData[$linkedId])) {
                            continue;
                        }
                        $storeIdKey = array_search(Store::DEFAULT_STORE_ID, $storesData[$linkedId], true);
                        if ($storeIdKey !== false) {
                            $stores = $this->storeManager->getStores(false, true);
                            $storeId = current($stores)->getId();
                            $storeCode = key($stores);
                        } else {
                            $storeId = current($storesData[$linkedId]);
                            $storeCode = $this->storeManager->getStore($storeId)->getCode();
                        }
                        $item->setData('_first_store_id', $storeId);
                        $item->setData('store_code', $storeCode);
                        $item->setData('store_id', $storesData[$linkedId]);
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * Add field filter to collection
     * @param array|string $field
     * @param string|int|array|null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition, false);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add filter by store
     * @param int|array|Store $store
     * @param bool $withAdmin
     * @return $this
     */
    abstract public function addStoreFilter($store, $withAdmin = true);

    /**
     * Perform adding filter by store
     * @param int|array|Store $store
     * @param bool $withAdmin
     * @return void
     */
    protected function performAddStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof Store) {
            $store = [$store->getId()];
        }

        if (!is_array($store)) {
            $store = [$store];
        }

        if ($withAdmin) {
            $store[] = Store::DEFAULT_STORE_ID;
        }

        $this->addFilter('store', ['in' => $store], 'public');
    }

    /**
     * Join store relation table if there is store filter
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    protected function joinStoreRelationTable($tableName, $linkField)
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable($tableName)],
                'main_table.' . $linkField . ' = store_table.' . $linkField,
                []
            )->group(
                'main_table.' . $linkField
            );
        }
        parent::_renderFiltersBefore();
    }

    /**
     * Get SQL for get record count
     * Extra GROUP BY strip added.
     * @return SelectAlias
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(SelectAlias::GROUP);
        return $countSelect;
    }
}
