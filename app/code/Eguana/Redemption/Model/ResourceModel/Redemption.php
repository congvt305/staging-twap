<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/10/20
 * Time: 4:20 PM
 */
namespace Eguana\Redemption\Model\ResourceModel;

use Eguana\Redemption\Api\Data\RedemptionInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Redemption Resource Model
 *
 * Class Redemption
 */
class Redemption extends AbstractDb
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \string[][]
     */
    protected $_associatedEntitiesMap = [
        'counter' => [
            'associations_table' => 'eguana_redemption_counter',
            'redemption_id_field' => 'redemption_id',
            'offline_store_id_field' => 'offline_store_id',
        ],
    ];

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
        parent::__construct($context, $connectionName);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eguana_redemption', 'redemption_id');
    }

    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(RedemptionInterface::class)->getEntityConnection();
    }

    /**
     * Perform operations before object save
     *
     * @param AbstractModel $object
     * @return $this
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        try {
            if (!$this->getIsUniqueRedemptionToStores($object)) {
                throw new LocalizedException(
                    __('A Redemption identifier with the same properties already exists in the selected store.')
                );
            }
        } catch (Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $this;
    }

    /**
     * @param AbstractModel $object
     * @param mixed $value
     * @param null $field
     * @return bool|int|string
     * @throws LocalizedException
     */
    private function getRedemptionId(AbstractModel $object, $value, $field = null)
    {
        try {
            $entityMetadata = $this->metadataPool->getMetadata(RedemptionInterface::class);
            if (!is_numeric($value) && $field === null) {
                $field = RedemptionInterface::REDEMPTION_ID;
            } elseif (!$field) {
                $field = $entityMetadata->getIdentifierField();
            }
            $entityId = $value;
            if ($field != $entityMetadata->getIdentifierField() || $object->getStoreId()) {
                $select = $this->_getLoadSelect($field, $value, $object);
                $select->reset(Select::COLUMNS)
                    ->columns($this->getMainTable() . '.' . $entityMetadata->getIdentifierField())
                    ->limit(1);
                $result = $this->getConnection()->fetchCol($select);
                $entityId = count($result) ? $result[0] : false;
            }
            return $entityId;
        } catch (Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $entityId;
    }

    /**
     * Load an object
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param null $field field to load by (defaults to model id)
     * @return $this
     * @throws LocalizedException
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $redemptionId = $this->getRedemptionId($object, $value, $field);
        if ($redemptionId) {
            $this->entityManager->load($object, $redemptionId);
        }
        return $this;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param AbstractModel $object
     * @return Select
     * @throws LocalizedException
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        try {
            $entityMetadata = $this->metadataPool->getMetadata(RedemptionInterface::class);
            $linkField = $entityMetadata->getLinkField();
            $select = parent::_getLoadSelect($field, $value, $object);
            if ($object->getStoreId()) {
                $stores = [(int)$object->getStoreId(), Store::DEFAULT_STORE_ID];
                $select->join(
                    ['ems' => $this->getTable('eguana_redemption_store')],
                    $this->getMainTable() . '.' . $linkField . ' = ems.' . $linkField,
                    ['store_id']
                )
                    ->where('is_active = ?', 1)
                    ->where('ems.store_id in (?)', $stores)
                    ->limit(1);
            }
            return $select;
        } catch (Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $select;
    }

    /**
     * @param AbstractModel $object
     * @return bool
     * @throws LocalizedException
     */
    public function getIsUniqueRedemptionToStores(AbstractModel $object)
    {
        try {
            $entityMetadata = $this->metadataPool->getMetadata(RedemptionInterface::class);
            $linkField = $entityMetadata->getLinkField();
            if ($this->storeManager->hasSingleStore()) {
                $stores = [Store::DEFAULT_STORE_ID];
            } else {
                $stores = (array)$object->getData('stores');
            }
            $select = $this->getConnection()->select()
                ->from(['em' => $this->getMainTable()])
                ->join(
                    ['ems' => $this->getTable('eguana_redemption_store')],
                    'em.' . $linkField . ' = ems.' . $linkField,
                    []
                )
                ->where('em.redemption_id = ?', $object->getData('redemption_id'))
                ->where('ems.store_id IN (?)', $stores);
            if ($object->getId()) {
                $select->where('em.' . $entityMetadata->getIdentifierField() . ' <> ?', $object->getId());
            }
            if ($this->getConnection()->fetchRow($select)) {
                return false;
            }
            return true;
        } catch (Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return true;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     * @throws LocalizedException
     */
    public function lookupStoreIds($id)
    {
        try {
            $connection = $this->getConnection();
            $entityMetadata = $this->metadataPool->getMetadata(RedemptionInterface::class);
            $linkField = $entityMetadata->getLinkField();
            $select = $connection->select()
                ->from(['ems' => $this->getTable('eguana_redemption_store')], 'store_id')
                ->join(
                    ['em' => $this->getMainTable()],
                    'ems.' . $linkField . ' = em.' . $linkField,
                    []
                )
                ->where('em.' . $entityMetadata->getIdentifierField() . ' = :redemption_id');
            return $connection->fetchCol($select, ['redemption_id' => (int)$id]);
        } catch (Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $connection->fetchCol($select, ['redemption_id' => (int)$id]);
    }

    /**
     * Get counter ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     * @throws LocalizedException
     */
    public function lookupCounterIds($id)
    {
        try {
            $connection = $this->getConnection();
            $entityMetadata = $this->metadataPool->getMetadata(RedemptionInterface::class);
            $linkField = $entityMetadata->getLinkField();
            $select = $connection->select()
                ->from(['ems' => $this->getTable('eguana_redemption_counter')], 'offline_store_id')
                ->join(
                    ['em' => $this->getMainTable()],
                    'ems.' . $linkField . ' = em.' . $linkField,
                    []
                )
                ->where('em.' . $entityMetadata->getIdentifierField() . ' = :redemption_id');
            return $connection->fetchCol($select, ['redemption_id' => (int)$id]);
        } catch (Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * _aftersave method
     * This method is used to seve data and it is using the functionality to store multiple counter id in separate table
     *
     * @param AbstractModel $object
     * @return $this|Redemption
     * @throws \Exception
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->entityManager->save($object);
        $counterIds = $object->getData('offline_store_id');
        if ($counterIds) {
            if (!is_array($counterIds)) {
                $counterIds = explode(',', (string) $counterIds);
            }
            $this->bindRuleToEntity($object->getId(), $counterIds, 'counter');
        }

        parent::_afterSave($object);
        return $this;
    }

    /**
     * bindRuleToEntity
     *
     * @param $ruleIds
     * @param $entityIds
     * @param $entityType
     * @return $this
     * @throws LocalizedException
     */
    public function bindRuleToEntity($ruleIds, $entityIds, $entityType)
    {
        $this->getConnection()->beginTransaction();

        try {
            $this->_multiplyBunchInsert($ruleIds, $entityIds, $entityType);
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        $this->getConnection()->commit();

        return $this;
    }

    /**
     * Retrieve correspondent entity information (associations table name, columns names)
     * of rule's associated entity by specified entity type
     *
     * @param string $entityType
     * @return array
     * @throws LocalizedException
     */
    protected function _getAssociatedEntityInfo($entityType)
    {
        if (isset($this->_associatedEntitiesMap[$entityType])) {
            return $this->_associatedEntitiesMap[$entityType];
        }

        throw new LocalizedException(
            __('There is no information about associated entity type "%1".', $entityType)
        );
    }

    /**
     * Multiply rule ids by entity ids and insert
     * @param $ruleIds
     * @param $entityIds
     * @param $entityType
     * @return $this
     * @throws LocalizedException
     */
    protected function _multiplyBunchInsert($ruleIds, $entityIds, $entityType)
    {
        if (empty($ruleIds) || empty($entityIds)) {
            return $this;
        }
        if (!is_array($ruleIds)) {
            $ruleIds = [(int)$ruleIds];
        }
        if (!is_array($entityIds)) {
            $entityIds = [(int)$entityIds];
        }
        $data = [];
        $count = 0;
        $entityInfo = $this->_getAssociatedEntityInfo($entityType);
        foreach ($ruleIds as $ruleId) {
            foreach ($entityIds as $entityId) {
                $data[] = [
                    $entityInfo['offline_store_id_field'] => $entityId,
                    $entityInfo['redemption_id_field'] => $ruleId,
                ];
                $count++;
                if ($count % 1000 == 0) {
                    $this->getConnection()->insertOnDuplicate(
                        $this->getTable($entityInfo['associations_table']),
                        $data,
                        [$entityInfo['redemption_id_field']]
                    );
                    $data = [];
                }
            }
        }
        if (!empty($data)) {
            $this->getConnection()->insertOnDuplicate(
                $this->getTable($entityInfo['associations_table']),
                $data,
                [$entityInfo['redemption_id_field']]
            );
        }

        $this->getConnection()->delete(
            $this->getTable($entityInfo['associations_table']),
            $this->getConnection()->quoteInto(
                $entityInfo['redemption_id_field'] . ' IN (?) AND ',
                $ruleIds
            ) . $this->getConnection()->quoteInto(
                $entityInfo['offline_store_id_field'] . ' NOT IN (?)',
                $entityIds
            )
        );
        return $this;
    }
}
