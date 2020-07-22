<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/16/20
 * Time: 12:55 AM
 */
namespace Eguana\Magazine\Model\ResourceModel;

use Eguana\Magazine\Api\Data\MagazineInterface;
use Magento\Framework\DB\Adapter\AdapterInterface as AdapterInterfaceAlias;
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
 * Main class to load data from db
 * Class Magazine
 */
class Magazine extends AbstractDb
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
     * @var LoggerInterface;
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
     * Constructer for this class
     */
    protected function _construct()
    {
        $this->_init('eguana_magazine', 'entity_id');
    }

    /**
     * For connection
     * @return false|AdapterInterfaceAlias
     */
    public function getConnection()
    {
        try {
            return $this->metadataPool->getMetadata(MagazineInterface::class)->getEntityConnection();
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * @param AbstractModel $object
     * @return $this|Magazine
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if (!$this->getIsUniqueMagazineToStores($object)) {
            throw new LocalizedException(
                __('A Magazine identifier with the same properties already exists in the selected store.')
            );
        }
        return $this;
    }

    /**
     * Get event id
     * @param AbstractModel $object
     * @param $value
     * @param null $field
     * @return bool|int|mixed|string
     */
    private function getEventId(AbstractModel $object, $value, $field = null)
    {
        try {
            $entityMetadata = $this->metadataPool->getMetadata(MagazineInterface::class);
            if (!is_numeric($value) && $field === null) {
                $field = 'title';
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
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $entityId;
    }

    /**
     * Load a object
     * @param AbstractModel $object
     * @param mixed $value
     * @param null $field
     * @return $this|Magazine
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
     * Load select data
     * @param string $field
     * @param mixed $value
     * @param AbstractModel $object
     * @return Select
     */
    public function _getLoadSelect($field, $value, $object)
    {
        try {
            $entityMetadata = $this->metadataPool->getMetadata(MagazineInterface::class);
            $linkField = $entityMetadata->getLinkField();

            $select = parent::_getLoadSelect($field, $value, $object);

            if ($object->getStoreId()) {
                $stores = [(int)$object->getStoreId(), Store::DEFAULT_STORE_ID];
                $select->join(
                    ['ems' => $this->getTable('eguana_magazine_store')],
                    $this->getMainTable() . '.' . $linkField . ' = ems.' . $linkField,
                    ['store_id']
                )
                ->where('is_active = ?', 1)
                ->where('ems.store_id in (?)', $stores)
                ->limit(1);
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $select;
    }

    /**
     * Get Unique Magazine
     * @param AbstractModel $object
     * @return bool
     */
    public function getIsUniqueMagazineToStores(AbstractModel $object)
    {
        try {
            $entityMetadata = $this->metadataPool->getMetadata(MagazineInterface::class);
            $linkField = $entityMetadata->getLinkField();
            if ($this->_storeManager->hasSingleStore()) {
                $stores = [Store::DEFAULT_STORE_ID];
            } else {
                $stores = (array)$object->getData('stores');
            }

            $select = $this->getConnection()->select()
            ->from(['em' => $this->getMainTable()])
            ->join(
                ['ems' => $this->getTable('eguana_magazine_store')],
                'em.' . $linkField . ' = ems.' . $linkField,
                []
            )
            ->where('em.title = ?', $object->getData('title'))
            ->where('ems.store_id IN (?)', $stores);

            if ($object->getId()) {
                $select->where('em.' . $entityMetadata->getIdentifierField() . ' <> ?', $object->getId());
            }

            if ($this->getConnection()->fetchRow($select)) {
                return false;
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return true;
    }

    /**
     * Get store ids to which specified item is assigned
     * @param $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        try {
            $connection = $this->getConnection();

            $entityMetadata = $this->metadataPool->getMetadata(MagazineInterface::class);
            $linkField = $entityMetadata->getLinkField();

            $select = $connection->select()
            ->from(['ems' => $this->getTable('eguana_magazine_store')], 'store_id')
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
     * For Save
     * @param AbstractModel $object
     * @return $this|Magazine
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
     * For delete
     * @param AbstractModel $object
     * @return $this|Magazine
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
