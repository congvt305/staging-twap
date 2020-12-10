<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/11/20
 * Time: 4:00 PM
 */
declare(strict_types=1);

namespace Eguana\NewsBoard\Model\ResourceModel;

use Eguana\NewsBoard\Api\Data\NewsInterface;
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
 * Class News
 */
class News extends AbstractDb
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
        $this->_init('eguana_news', 'news_id');
    }

    /**
     * Get connection
     *
     * @return false|AdapterInterface
     */
    public function getConnection()
    {
        $connection = '';
        try {
            $connection = $this->metadataPool->getMetadata(NewsInterface::class)
                ->getEntityConnection();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $connection;
    }

    /**
     * Process news data before saving
     *
     * @param AbstractModel $object
     * @return News
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if (!$this->isValidEventIdentifier($object)) {
            throw new LocalizedException(
                __(
                    "The News URL key can't use capital letters or disallowed symbols. "
                    . "Remove the letters and symbols and try again."
                )
            );
        }

        if ($this->isNumericEventIdentifier($object)) {
            throw new LocalizedException(
                __("The news URL key can't use only numbers. Add letters or words and try again.")
            );
        }
        return parent::_beforeSave($object);
    }

    /**
     * Get News ID
     *
     * @param AbstractModel $object
     * @param $value
     * @param null $field
     * @return false|int|mixed|string
     */
    private function getEventId(AbstractModel $object, $value, $field = null)
    {
        $newsId = '';
        try {
            $entityMetadata = $this->metadataPool->getMetadata(NewsInterface::class);

            if (!is_numeric($value) && $field === null) {
                $field = 'news_id';
            } elseif (!$field) {
                $field = $entityMetadata->getIdentifierField();
            }

            $newsId = $value;
            if ($field != $entityMetadata->getIdentifierField() || $object->getStoreId()) {
                $select = $this->_getLoadSelect($field, $value, $object);
                $select->reset(Select::COLUMNS)
                    ->columns($this->getMainTable() . '.' . $entityMetadata->getIdentifierField())
                    ->limit(1);
                $result = $this->getConnection()->fetchCol($select);
                $newsId = count($result) ? $result[0] : false;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $newsId;
    }

    /**
     * Load an object
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param null $field
     * @return $this|News
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $newsId = $this->getEventId($object, $value, $field);
        if ($newsId) {
            $this->entityManager->load($object, $newsId);
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
     * @throws LocalizedException
     */
    protected function _getLoadSelect($field, $value, $object) : Select
    {
        $entityMetadata = '';
        try {
            $entityMetadata = $this->metadataPool->getMetadata(NewsInterface::class);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $linkField = $entityMetadata->getLinkField();

        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $storeIds = [
                Store::DEFAULT_STORE_ID,
                (int)$object->getStoreId(),
            ];

            $select->join(
                ['eers' => $this->getTable('eguana_news_store')],
                $this->getMainTable() . '.' . $linkField . ' = eguana_news_store.' . $linkField,
                []
            )
                ->where('is_active = ?', 1)
                ->where('eers.store_id IN (?)', $storeIds)
                ->limit(1);
        }

        return $select;
    }

    /**
     * Retrieve load select with filter by news id, store and activity
     *
     * @param $id
     * @param $store
     * @param null $isActive
     * @return Select
     * @throws LocalizedException
     */
    protected function _getLoadByIdSelect($id, $store, $isActive = null) : Select
    {
        $entityMetadata = '';
        try {
            $entityMetadata = $this->metadataPool->getMetadata(NewsInterface::class);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $linkField = $entityMetadata->getLinkField();

        $select = $this->getConnection()->select()
            ->from(['eer' => $this->getMainTable()])
            ->join(
                ['eers' => $this->getTable('eguana_news_store')],
                'eer.' . $linkField . ' = eers.' . $linkField,
                []
            )
            ->where('eer.news_id = ?', $id)
            ->where('eers.store_id IN (?)', $store);

        if ($isActive !== null) {
            $select->where('eer.is_active = ?', $isActive);
        }

        return $select;
    }

    /**
     *  Check whether news identifier is numeric
     *
     * @param AbstractModel $object
     * @return bool
     */
    private function isNumericEventIdentifier(AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     *  Check whether news identifier is valid
     *
     * @param AbstractModel $object
     * @return bool
     */
    private function isValidEventIdentifier(AbstractModel $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier'));
    }

    /**
     * Check if news id exist for specific store
     * return news id if news exists
     *
     * @param $newsId
     * @param $storeId
     * @return string
     * @throws LocalizedException
     */
    public function checkId($newsId, $storeId) : string
    {
        $entityMetadata = '';
        try {
            $entityMetadata = $this->metadataPool->getMetadata(NewsInterface::class);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $stores = [Store::DEFAULT_STORE_ID, $storeId];
        $select = $this->_getLoadByIdSelect($newsId, $stores, 1);
        $select->reset(Select::COLUMNS)
            ->columns('eer.' . $entityMetadata->getIdentifierField())
            ->order('eers.store_id DESC')
            ->limit(1);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param $newsId
     * @return array
     * @throws LocalizedException
     */
    public function lookupStoreIds($newsId) : array
    {
        $connection = $this->getConnection();
        $entityMetadata = '';
        try {
            $entityMetadata = $this->metadataPool->getMetadata(NewsInterface::class);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['eers' => $this->getTable('eguana_news_store')], 'store_id')
            ->join(
                ['eer' => $this->getMainTable()],
                'eers.' . $linkField . ' = eer.' . $linkField,
                []
            )
            ->where('eer.' . $entityMetadata->getIdentifierField() . ' = :news_id');
        return $connection->fetchCol($select, ['news_id' => (int)$newsId]);
    }

    /**
     * Get category ids to which specified item is assigned
     *
     * @param $newsId
     * @return array
     * @throws LocalizedException
     */
    public function lookCategoryIds($newsId) : array
    {
        $connection = $this->getConnection();
        $entityMetadata = '';
        try {
            $entityMetadata = $this->metadataPool->getMetadata(NewsInterface::class);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['eers' => $this->getTable('eguana_news_store')], 'category')
            ->join(
                ['eer' => $this->getMainTable()],
                'eers.' . $linkField . ' = eer.' . $linkField,
                []
            )
            ->where('eer.' . $entityMetadata->getIdentifierField() . ' = :news_id');
        return $connection->fetchCol($select, ['news_id' => (int)$newsId]);
    }

    /**
     * Save method
     *
     * @param AbstractModel $object
     * @return array|News|object
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
     * Delete news method
     *
     * @param AbstractModel $object
     * @return bool|News
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
