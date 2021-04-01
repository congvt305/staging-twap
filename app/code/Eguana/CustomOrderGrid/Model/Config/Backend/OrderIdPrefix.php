<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 22/3/21
 * Time: 3:10 PM
 */
declare(strict_types=1);

namespace Eguana\CustomOrderGrid\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\SalesSequence\Model\ResourceModel\Meta;
use Magento\SalesSequence\Model\ResourceModel\MetaFactory;
use Magento\SalesSequence\Model\ResourceModel\Profile;
use Magento\SalesSequence\Model\ResourceModel\ProfileFactory;
use Magento\Store\Api\StoreWebsiteRelationInterface;

/**
 * Plugin to load order prefix value and save its value in table sales_sequence_profile
 *
 * Class OrderIdPrefix
 */
class OrderIdPrefix extends Value
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var Meta
     */
    private $metaFactory;

    /**
     * @var Profile
     */
    private $profileFactory;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Http $request,
        Context $context,
        Registry $registry,
        AbstractDb $resourceCollection = null,
        MetaFactory $metaFactory,
        ProfileFactory $profileFactory,
        AbstractResource $resource = null,
        TypeListInterface $cacheTypeList,
        ScopeConfigInterface $config,
        StoreWebsiteRelationInterface $storeWebsiteRelation,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
        $this->request = $request;
        $this->metaFactory = $metaFactory->create();
        $this->profileFactory = $profileFactory->create();
        $this->storeWebsiteRelation = $storeWebsiteRelation;
    }

    /**
     * After load config field value
     *
     * @return OrderIdPrefix|void
     */
    protected function _afterLoad()
    {
        $metaIds = $this->loadMetaByStore();
        $metaInfo = $this->getProfileByMeta($metaIds);
        $this->setValue($metaInfo['prefix']);
    }

    /**
     * After save config value
     *
     * @return OrderIdPrefix
     */
    public function afterSave()
    {
        $metaIds = $this->loadMetaByStore();
        $prefix = $this->getValue() ? $this->getValue() : null;
        $this->updateProfileByMeta($metaIds, $prefix);
        return parent::afterSave();
    }

    /**
     * Load meta by store id
     *
     * @return array
     */
    public function loadMetaByStore()
    {
        $websiteId = (int) $this->request->getParam('website');
        if ($websiteId) {
            $storeIds = $this->storeWebsiteRelation->getStoreByWebsiteId($websiteId);
        } else {
            $storeIds = (int) $this->request->getParam('store');
            $storeIds = $storeIds ? [$storeIds] : [0];
        }
        try {
            $connection = $this->metaFactory->getConnection();
            $bind = ['store_id' => implode(',', $storeIds)];
            $select = $connection->select()->from(
                $this->metaFactory->getMainTable(),
                ['meta_id']
            )->where(
                'store_id IN (:store_id)'
            )->where('entity_type = ?', 'order');

            return $connection->fetchCol($select, $bind);
        } catch (\Exception $exception) {
            $this->_logger->error($exception->getMessage());
            return [];
        }
    }

    /**
     * Get profile prefix by metaids
     *
     * @param array $metaIds To update profile prefix
     * @param string $prefix sales sequence prefix to be updated.
     * @return array
     */
    public function getProfileByMeta($metaIds)
    {
        try {
            $connection = $this->profileFactory->getConnection();
            $select = $connection->select()
                ->from(
                    $connection->getTableName($this->profileFactory->getMainTable()),
                    ['prefix', 'suffix']
                )
                ->where('meta_id IN (?)', $metaIds);
            return $connection->fetchRow($select);
        } catch (\Exception $exception) {
            $this->_logger->error($exception->getMessage());
            return [];
        }
    }

    /**
     * Update profile prefix by metaids
     *
     * @param array $ids to update profile prefix
     * @param null $prefix sales sequence prefix to be updated.
     * @return void
     */
    public function updateProfileByMeta($ids, $prefix = null)
    {
        try {
            $this->profileFactory->getConnection()->update(
                $this->profileFactory->getConnection()->getTableName($this->profileFactory->getMainTable()),
                ['prefix' => $prefix],
                ['meta_id IN (?)' => $ids]
            );
        } catch (\Exception $exception) {
            $this->_logger->error($exception->getMessage());
        }
    }
}
