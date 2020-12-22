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

use Eguana\Faq\Api\Data\FaqInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context as ContextAlias;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Faq
 *
 * Eguana\Faq\Model\ResourceModel
 */
class Faq extends AbstractDb
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
     * @param ContextAlias $context
     * @param StoreManagerInterface $storeManager
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param string $connectionName
     */
    public function __construct(
        ContextAlias $context,
        StoreManagerInterface $storeManager,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        $this->storeManager = $storeManager;
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
        $this->_init('eguana_faq', 'entity_id');
    }

    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(FaqInterface::class)->getEntityConnection();
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
        if (!$this->getIsUniqueBlockToStores($object)) {
            throw new LocalizedException(
                __('A block identifier with the same properties already exists in the selected store.')
            );
        }
        return $this;
    }

    /**
     * @param AbstractModel $object
     * @param mixed $value
     * @param null $field
     * @return bool|int|string
     * @throws LocalizedException
     * @throws \Exception
     */
    private function getFaqId(AbstractModel $object, $value, $field = null)
    {
        $entityMetadata = $this->metadataPool->getMetadata(FaqInterface::class);
        if (!is_numeric($value) && $field === null) {
            $field = FaqInterface::ENTITY_ID;
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
    }

    /**
     * Load an object
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $faqId = $this->getFaqId($object, $value, $field);
        if ($faqId) {
            $this->entityManager->load($object, $faqId);
        }
        return $this;
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Eguana\Faq\Model\Faq $object
     * @return Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $entityMetadata = $this->metadataPool->getMetadata(FaqInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $stores = [(int)$object->getStoreId(), Store::DEFAULT_STORE_ID];

            $select->join(
                ['efs' => $this->getTable('eguana_faq_store')],
                $this->getMainTable() . '.' . $linkField . ' = efs.' . $linkField,
                ['store_id']
            )
                ->where('is_active = ?', 1)
                ->where('efs.store_id in (?)', $stores)
                ->order('store_id DESC')
                ->limit(1);
        }

        return $select;
    }

    /**
     * Check for unique of identifier of block to selected store(s).
     *
     * @param AbstractModel $object
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsUniqueBlockToStores(AbstractModel $object)
    {
        $entityMetadata = $this->metadataPool->getMetadata(FaqInterface::class);
        $linkField = $entityMetadata->getLinkField();

        if ($this->storeManager->isSingleStoreMode()) {
            $stores = [Store::DEFAULT_STORE_ID];
        } else {
            $stores = (array)$object->getData('store_id');
        }

        $select = $this->getConnection()->select()
            ->from(['ef' => $this->getMainTable()])
            ->join(
                ['efs' => $this->getTable('eguana_faq_store')],
                'ef.' . $linkField . ' = efss.' . $linkField,
                []
            )
            ->where('ef.entity_id = ?', $object->getData(FaqInterface::ENTITY_ID))
            ->where('efs.store_id IN (?)', $stores);

        if ($object->getId()) {
            $select->where('ef.' . $entityMetadata->getIdentifierField() . ' <> ?', $object->getId());
        }

        if ($this->getConnection()->fetchRow($select)) {
            return false;
        }

        return true;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(FaqInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['efs' => $this->getTable('eguana_faq_store')], 'store_id')
            ->join(
                ['ef' => $this->getMainTable()],
                'efs.' . $linkField . ' = ef.' . $linkField,
                []
            )
            ->where('ef.' . $entityMetadata->getIdentifierField() . ' = :entity_id');

        return $connection->fetchCol($select, ['entity_id' => (int)$id]);
    }

    /**
     * Get category ids to which specified item is assigned
     *
     * @param $faqId
     * @return array
     * @throws LocalizedException
     */
    public function lookCategoryIds($faqId) : array
    {
        try {
            $connection = $this->getConnection();
            $entityMetadata = $this->metadataPool->getMetadata(FaqInterface::class);
            $linkField = $entityMetadata->getLinkField();

            $select = $connection->select()
                ->from(['efs' => $this->getTable('eguana_faq_store')], 'category')
                ->join(
                    ['ef' => $this->getMainTable()],
                    'efs.' . $linkField . ' = ef.' . $linkField,
                    []
                )
                ->where('ef.' . $entityMetadata->getIdentifierField() . ' = :entity_id');
            return $connection->fetchCol($select, ['entity_id' => (int)$faqId]);
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @param AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    public function save(AbstractModel $object)
    {
        $this->entityManager->save($object);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }
}
