<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/11/20
 * Time: 5:00 PM
 */
declare(strict_types=1);

namespace Eguana\NewsBoard\Model\ResourceModel;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as AbstractCollectionAlias;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Abstract class for news
 * Class AbstractCollection
 */
abstract class AbstractCollection extends AbstractCollectionAlias
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $managerInterface
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $managerInterface,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->logger       = $logger;
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $managerInterface,
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
    public function performAfterLoad($tableName, $linkField) : void
    {
        try {
            $linkedIds = $this->getColumnValues($linkField);

            if (!empty($linkedIds)) {
                $connection = $this->getConnection();
                $select = $connection->select()->from([
                    'eguana_news_store' => $this->getTable($tableName)
                ])
                    ->where('eguana_news_store.' . $linkField . ' IN (?)', $linkedIds);
                $result = $connection->fetchAll($select);
                if ($result) {
                    $storesData = [];
                    $categories = [];
                    foreach ($result as $storeData) {
                        $storesData[$storeData[$linkField]][] = $storeData['store_id'];
                        $categories[$storeData[$linkField]][] = $storeData['category'];
                    }

                    foreach ($this as $item) {
                        $linkedId = $item->getData($linkField);

                        if (!isset($storesData[$linkedId])) {
                            continue;
                        }
                        $storeIdKey = array_search(
                            Store::DEFAULT_STORE_ID,
                            $storesData[$linkedId],
                            true
                        );

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
                        $item->setData('category', $categories[$linkedId]);
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
     * @param null $condition
     * @return AbstractCollection|mixed
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
     * @return mixed
     */
    abstract public function addStoreFilter($store, $withAdmin = true);

    /**
     * Perform adding filter by store
     *
     * @param int|array|Store $store
     * @param bool $withAdmin
     * @return void
     */
    protected function performAddStoreFilter($store, $withAdmin = true) : void
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
    protected function joinStoreRelationTable($tableName, $linkField) : void
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
    public function getSelectCountSql() : Select
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(Select::GROUP);

        return $countSelect;
    }
}
