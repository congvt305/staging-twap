<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 26/10/20
 * Time: 6:20 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\Model\ResourceModel;

use Eguana\EventReservation\Api\Data\UserReservationInterface;
use Eguana\EventReservation\Model\UserReservation as UserReservationModel;
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
 * Class UserReservation
 */
class UserReservation extends AbstractDb
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
        $this->_init('eguana_user_event_reserve', 'user_reserve_id');
    }

    /**
     * Get connection
     *
     * @return false|AdapterInterface
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(UserReservationInterface::class)
            ->getEntityConnection();
    }

    /**
     * Get UserReservation ID
     *
     * @param AbstractModel $object
     * @param string $value
     * @param string|null $field
     * @return bool|int|string
     * @throws LocalizedException
     * @throws Exception
     */
    private function getUserReservationId(AbstractModel $object, $value, $field = null)
    {
        $entityMetadata = $this->metadataPool->getMetadata(UserReservationInterface::class);

        if (!is_numeric($value) && $field === null) {
            $field = 'user_reserve_id';
        } elseif (!$field) {
            $field = $entityMetadata->getIdentifierField();
        }

        $userReservationId = $value;
        if ($field != $entityMetadata->getIdentifierField() || $object->getStoreId()) {
            $select = $this->_getLoadSelect($field, $value, $object);
            $select->reset(Select::COLUMNS)
                ->columns($this->getMainTable() . '.' . $entityMetadata->getIdentifierField())
                ->limit(1);
            $result = $this->getConnection()->fetchCol($select);
            $userReservationId = count($result) ? $result[0] : false;
        }

        return $userReservationId;
    }

    /**
     * Load an object
     *
     * @param UserReservationModel|AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this|UserReservation
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $userReservationId = $this->getUserReservationId($object, $value, $field);
        if ($userReservationId) {
            $this->entityManager->load($object, $userReservationId);
        }
        return $this;
    }

    /**
     * Save method
     *
     * @param AbstractModel $object
     * @return array|UserReservation|object
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
     * Delete UserReservation method
     *
     * @param AbstractModel $object
     * @return bool|UserReservation
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
