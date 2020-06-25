<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Model\ResourceModel;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategyInterfaceAlias;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactoryInterfaceAlias;
use Magento\Framework\DB\Adapter\AdapterInterface as AdapterInterfaceAlias;
use Magento\Framework\DB\Select as SelectAlias;
use Magento\Framework\EntityManager\MetadataPool as MetadataPoolAlias;
use Magento\Framework\Event\ManagerInterface as ManagerInterfaceAlias;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb as AbstractDbAlias;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterfaceAlias;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as AbstractCollectionExtend;
use Psr\Log\LoggerInterface as LoggerInterfaceAlias;

/**
 * Abstract collection of Eguana Faq
 */
abstract class AbstractCollection extends AbstractCollectionExtend
{
    /**
     * Store manager
     * @var StoreManagerInterfaceAlias
     */
    private $storeManager;

    /**
     * @var MetadataPoolAlias
     */
    protected $metadataPool;

    /**
     * @param EntityFactoryInterfaceAlias $entityFactory
     * @param LoggerInterfaceAlias $logger
     * @param FetchStrategyInterfaceAlias $fetchStrategy
     * @param ManagerInterfaceAlias $eventManager
     * @param StoreManagerInterfaceAlias $storeManager
     * @param MetadataPoolAlias $metadataPool
     * @param AdapterInterfaceAlias|null $connection
     * @param AbstractDbAlias|null $resource
     */
    public function __construct(
        EntityFactoryInterfaceAlias $entityFactory,
        LoggerInterfaceAlias $logger,
        FetchStrategyInterfaceAlias $fetchStrategy,
        ManagerInterfaceAlias $eventManager,
        StoreManagerInterfaceAlias $storeManager,
        MetadataPoolAlias $metadataPool,
        AdapterInterfaceAlias $connection = null,
        AbstractDbAlias $resource = null
    ) {
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    protected function performAfterLoad($tableName, $linkField)
    {
        $linkedIds = $this->getColumnValues($linkField);
        if (count($linkedIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['eguana_faq_store' => $this->getTable($tableName)])
                ->where('eguana_faq_store.' . $linkField . ' IN (?)', $linkedIds);
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
    }

    /**
     * Add field filter to collection
     *
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
     *
     * @param int|array|Store $store
     * @param bool $withAdmin
     * @return $this
     */
    abstract public function addStoreFilter($store, $withAdmin = true);

    /**
     * Perform adding filter by store
     *
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
     *
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
     *
     * Extra GROUP BY strip added.
     *
     * @return SelectAlias
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(SelectAlias::GROUP);

        return $countSelect;
    }
}
