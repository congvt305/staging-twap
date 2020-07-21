<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: danish
 * Date: 11/26/19
 * Time: 5:33 PM
 */
namespace Eguana\StoreLocator\Model\ResourceModel;

use Eguana\StoreLocator\Api\Data\StoreInfoInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Resource model
 *
 * Class StoreInfo
 *  Eguana\StoreLocator\Model\ResourceModel
 */
class StoreInfo extends AbstractDb
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
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        $this->_storeManager = $storeManager;
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('storeinfo', 'entity_id');
    }

    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(StoreInfoInterface::class)->getEntityConnection();
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
        if (!$this->getIsUniqueStoreToStores($object)) {
            throw new LocalizedException(
                __('A Store Locator identifier with the same properties already exists in the selected store.')
            );
        }
        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return bool
     * @throws LocalizedException
     */
    public function getIsUniqueStoreToStores(AbstractModel $object)
    {
        $entityMetadata = $this->metadataPool->getMetadata(StoreInfoInterface::class);
        $linkField = $entityMetadata->getLinkField();
        if ($this->_storeManager->hasSingleStore()) {
            $stores = [Store::DEFAULT_STORE_ID];
        } else {
            $stores = (array)$object->getData('stores');
        }
        $select = $this->getConnection()->select()
            ->from(['sl' => $this->getMainTable()])
            ->join(
                ['sls' => $this->getTable('eguana_storelocator_store')],
                'sl.' . $linkField . ' = sls.' . $linkField,
                []
            )
            ->where('sl.title = ?', $object->getData('title'))
            ->where('sls.store_id IN (?)', $stores);
        if ($object->getId()) {
            $select->where('sl.' . $entityMetadata->getIdentifierField() . ' <> ?', $object->getId());
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

        $entityMetadata = $this->metadataPool->getMetadata(StoreInfoInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['sls' => $this->getTable('eguana_storelocator_store')], 'store_id')
            ->join(
                ['sl' => $this->getMainTable()],
                'sls.' . $linkField . ' = sl.' . $linkField,
                []
            )
            ->where('sl.' . $entityMetadata->getIdentifierField() . ' = :entity_id');

        return $connection->fetchCol($select, ['entity_id' => (int)$id]);
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
}
