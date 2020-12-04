<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/10/20
 * Time: 05:22 PM
 */
namespace Eguana\Redemption\Model\ResourceModel;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as AbstractCollectionAlias;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterfaceAlias;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\Store;

/**
 * Class AbstractCollection
 *
 */
abstract class AbstractCollection extends AbstractCollectionAlias
{
    /**
     * Store manager
     *
     * @var StoreManagerInterfaceAlias
     */
    private $storeManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $redemption
     * @param StoreManagerInterfaceAlias $storeManager
     * @param MetadataPool $metadataPool
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $redemption,
        StoreManagerInterfaceAlias $storeManager,
        MetadataPool $metadataPool,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $redemption,
            $connection,
            $resource
        );
    }

    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    public function performAfterLoad($tableName, $linkField)
    {
        try {
            $linkedIds = $this->getColumnValues($linkField);

            if (!empty($linkedIds)) {
                $connection = $this->getConnection();
                $select = $connection->select()->from(['eguana_redemption_store' => $this->getTable($tableName)])
                    ->where('eguana_redemption_store.' . $linkField . ' IN (?)', $linkedIds);
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
     * Perform operations after collection load for counter list
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    public function performAfterLoadForCounter($tableName, $linkField)
    {
        try {
            $linkedIds = $this->getColumnValues($linkField);

            if (!empty($linkedIds)) {
                $connection = $this->getConnection();
                $select = $connection->select()->from(['eguana_redemption_counter' => $this->getTable($tableName)])
                    ->where('eguana_redemption_counter.' . $linkField . ' IN (?)', $linkedIds);
                $result = $connection->fetchAll($select);
                if ($result) {
                    $storesData = [];
                    foreach ($result as $storeData) {
                        $storesData[$storeData[$linkField]][] = $storeData['offline_store_id'];
                    }
                    foreach ($this as $item) {
                        $linkedId = $item->getData($linkField);
                        $item->setData('offline_store_id', $storesData[$linkedId]);
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
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
     * Extra GROUP BY strip added.
     *
     * @return Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Select::GROUP);
        return $countSelect;
    }
}
