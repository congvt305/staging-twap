<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 15/10/20
 * Time: 4:07 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Model\ResourceModel;

use Eguana\EventReservation\Api\Data\EventInterface;
use Eguana\EventReservation\Model\Event as EventModel;
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
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * mysql resource class
 *
 * Class Event
 */
class Event extends AbstractDb
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
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param EntityManager $entityManager
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->logger           = $logger;
        $this->metadataPool     = $metadataPool;
        $this->storeManager     = $storeManager;
        $this->entityManager    = $entityManager;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eguana_event_reservation', 'event_id');
    }

    /**
     * Get connection
     *
     * @return false|AdapterInterface
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(EventInterface::class)
            ->getEntityConnection();
    }

    /**
     * Process event data before saving
     *
     * @param AbstractModel $object
     * @return Event
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if (!$this->isValidEventIdentifier($object)) {
            throw new LocalizedException(
                __(
                    "The event URL key can't use capital letters or disallowed symbols. "
                    . "Remove the letters and symbols and try again."
                )
            );
        }

        if ($this->isNumericEventIdentifier($object)) {
            throw new LocalizedException(
                __("The event URL key can't use only numbers. Add letters or words and try again.")
            );
        }
        return parent::_beforeSave($object);
    }

    /**
     * Get Event ID
     *
     * @param AbstractModel $object
     * @param string $value
     * @param string|null $field
     * @return bool|int|string
     * @throws LocalizedException
     * @throws Exception
     */
    private function getEventId(AbstractModel $object, $value, $field = null)
    {
        $entityMetadata = $this->metadataPool->getMetadata(EventInterface::class);

        if (!is_numeric($value) && $field === null) {
            $field = 'event_id';
        } elseif (!$field) {
            $field = $entityMetadata->getIdentifierField();
        }

        $eventId = $value;
        if ($field != $entityMetadata->getIdentifierField() || $object->getStoreId()) {
            $select = $this->_getLoadSelect($field, $value, $object);
            $select->reset(Select::COLUMNS)
                ->columns($this->getMainTable() . '.' . $entityMetadata->getIdentifierField())
                ->limit(1);
            $result = $this->getConnection()->fetchCol($select);
            $eventId = count($result) ? $result[0] : false;
        }

        return $eventId;
    }

    /**
     * Load an object
     *
     * @param EventModel|AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this|Event
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $eventId = $this->getEventId($object, $value, $field);
        if ($eventId) {
            $this->entityManager->load($object, $eventId);
        }
        return $this;
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param AbstractModel $object
     * @return Select
     */
    protected function _getLoadSelect($field, $value, $object) : Select
    {
        $entityMetadata = $this->metadataPool->getMetadata(EventInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $storeIds = [
                Store::DEFAULT_STORE_ID,
                (int)$object->getStoreId(),
            ];

            $select->join(
                ['eers' => $this->getTable('eguana_event_reservation_store')],
                $this->getMainTable() . '.' . $linkField . ' = eguana_event_reservation_store.' . $linkField,
                []
            )
                ->where('is_active = ?', 1)
                ->where('eers.store_id IN (?)', $storeIds)
                ->limit(1);
        }

        return $select;
    }

    /**
     * Retrieve load select with filter by event id, store and activity
     *
     * @param string $id
     * @param int|array $store
     * @param int $isActive
     * @return Select
     */
    protected function _getLoadByIdSelect($id, $store, $isActive = null) : Select
    {
        $entityMetadata = $this->metadataPool->getMetadata(EventInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $this->getConnection()->select()
            ->from(['eer' => $this->getMainTable()])
            ->join(
                ['eers' => $this->getTable('eguana_event_reservation_store')],
                'eer.' . $linkField . ' = eers.' . $linkField,
                []
            )
            ->where('eer.event_id = ?', $id)
            ->where('eers.store_id IN (?)', $store);

        if ($isActive !== null) {
            $select->where('eer.is_active = ?', $isActive);
        }

        return $select;
    }

    /**
     *  Check whether event identifier is numeric
     *
     * @param AbstractModel $object
     * @return bool
     */
    private function isNumericEventIdentifier(AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     *  Check whether event identifier is valid
     *
     * @param AbstractModel $object
     * @return bool
     */
    private function isValidEventIdentifier(AbstractModel $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier'));
    }

    /**
     * Check if event id exist for specific store
     * return event id if event exists
     *
     * @param int $eventId
     * @param int $storeId
     * @return string
     */
    public function checkId($eventId, $storeId) : string
    {
        $entityMetadata = $this->metadataPool->getMetadata(EventInterface::class);

        $stores = [Store::DEFAULT_STORE_ID, $storeId];
        $select = $this->_getLoadByIdSelect($eventId, $stores, 1);
        $select->reset(Select::COLUMNS)
            ->columns('eer.' . $entityMetadata->getIdentifierField())
            ->order('eers.store_id DESC')
            ->limit(1);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $eventId
     * @return array
     */
    public function lookupStoreIds($eventId) : array
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(EventInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['eers' => $this->getTable('eguana_event_reservation_store')], 'store_id')
            ->join(
                ['eer' => $this->getMainTable()],
                'eers.' . $linkField . ' = eer.' . $linkField,
                []
            )
            ->where('eer.' . $entityMetadata->getIdentifierField() . ' = :event_id');

        return $connection->fetchCol($select, ['event_id' => (int)$eventId]);
    }

    /**
     * Save method
     *
     * @param AbstractModel $object
     * @return array|Event|object
     */
    public function save(AbstractModel $object)
    {
        $return = [];
        try {
            $return = $this->entityManager->save($object);
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $return;
    }

    /**
     * Delete event method
     *
     * @param AbstractModel $object
     * @return bool|Event
     */
    public function delete(AbstractModel $object)
    {
        $return = false;
        try {
            $return = $this->entityManager->delete($object);
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $return;
    }
}
