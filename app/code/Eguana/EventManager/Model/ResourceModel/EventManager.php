<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/6/20
 * Time: 4:00 PM
 */
namespace Eguana\EventManager\Model\ResourceModel;

use Magento\Cms\Model\Block as BlockAlias;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Psr\Log\LoggerInterface;
use Eguana\EventManager\Api\Data\EventManagerInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Main class to load data from db
 *
 * Class EventManager
 */
class EventManager extends AbstractDb
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
        parent::__construct($context, $connectionName);
    }

    /**
     * Resource initialization
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eguana_event_manager', 'entity_id');
    }

    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(EventManagerInterface::class)->getEntityConnection();
    }

    /**
     * Perform operations before object save
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        try {
            if (!$this->getIsUniqueEventToStores($object)) {
                throw new LocalizedException(
                    __('A Event identifier with the same properties already exists in the selected store.')
                );
            }
            return $this;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * @param AbstractModel $object
     * @param mixed $value
     * @param null $field
     * @return bool|int|string
     */
    private function getEventId(AbstractModel $object, $value, $field = null)
    {
        try {
            $entityMetadata = $this->metadataPool->getMetadata(EventManagerInterface::class);
            if (!is_numeric($value) && $field === null) {
                $field = 'entity_id';
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
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * Load an object
     *
     * @param BlockAlias|AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $blockId = $this->getEventId($object, $value, $field);
        if ($blockId) {
            $this->entityManager->load($object, $blockId);
        }
        return $this;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param AbstractModel $object
     * @return Select
     */
    public function _getLoadSelect($field, $value, $object)
    {
        try {
            $entityMetadata = $this->metadataPool->getMetadata(EventManagerInterface::class);
            $linkField = $entityMetadata->getLinkField();

            $select = parent::_getLoadSelect($field, $value, $object);

            if ($object->getStoreId()) {
                $stores = [(int)$object->getStoreId(), Store::DEFAULT_STORE_ID];

                $select->join(
                    ['ems' => $this->getTable('eguana_event_manager_store')],
                    $this->getMainTable() . '.' . $linkField . ' = ems.' . $linkField,
                    ['store_id']
                )
                    ->where('is_active = ?', 1)
                    ->where('ems.store_id in (?)', $stores)
                    ->limit(1);
            }
            return $select;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * @param AbstractModel $object
     * @return bool
     */
    public function getIsUniqueEventToStores(AbstractModel $object)
    {
        try {
            $entityMetadata = $this->metadataPool->getMetadata(EventManagerInterface::class);
            $linkField = $entityMetadata->getLinkField();

            if ($this->_storeManager->hasSingleStore()) {
                $stores = [Store::DEFAULT_STORE_ID];
            } else {
                $stores = (array)$object->getData('stores');
            }

            $select = $this->getConnection()->select()
                ->from(['em' => $this->getMainTable()])
                ->join(
                    ['ems' => $this->getTable('eguana_event_manager_store')],
                    'em.' . $linkField . ' = ems.' . $linkField,
                    []
                )
                ->where('em.entity_id = ?', $object->getData('entity_id'))
                ->where('ems.store_id IN (?)', $stores);

            if ($object->getId()) {
                $select->where('em.' . $entityMetadata->getIdentifierField() . ' <> ?', $object->getId());
            }

            if ($this->getConnection()->fetchRow($select)) {
                return false;
            }
            return true;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        try {
            $connection = $this->getConnection();

            $entityMetadata = $this->metadataPool->getMetadata(EventManagerInterface::class);
            $linkField = $entityMetadata->getLinkField();

            $select = $connection->select()
                ->from(['ems' => $this->getTable('eguana_event_manager_store')], 'store_id')
                ->join(
                    ['em' => $this->getMainTable()],
                    'ems.' . $linkField . ' = em.' . $linkField,
                    []
                )
                ->where('em.' . $entityMetadata->getIdentifierField() . ' = :entity_id');

            return $connection->fetchCol($select, ['entity_id' => (int)$id]);
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    public function save(AbstractModel $object)
    {
        try {
            $this->entityManager->save($object);
            return $this;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * @param AbstractModel $object
     * @return $this|EventManager
     */
    public function delete(AbstractModel $object)
    {
        try {
            $this->entityManager->delete($object);
            return $this;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }
}
