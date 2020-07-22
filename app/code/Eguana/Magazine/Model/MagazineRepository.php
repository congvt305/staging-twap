<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/16/20
 * Time: 12:51 AM
 */
namespace Eguana\Magazine\Model;

use Eguana\Magazine\Api\Data;
use Eguana\Magazine\Api\MagazineRepositoryInterface;
use Eguana\Magazine\Model\Magazine as MagazineAlias;
use Eguana\Magazine\Model\ResourceModel\Magazine as ResourceMagazine;
use Eguana\Magazine\Model\ResourceModel\Magazine\CollectionFactory as MagazineCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\DataObject as DataObjectAlias;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Eguana\Magazine\Api\Data\MagazineInterfaceFactory;
use Eguana\Magazine\Api\Data\MagazineInterface;

/**
 * Class for eguana_magazine db table
 *
 * Class MagazineRepository
 */
class MagazineRepository implements MagazineRepositoryInterface
{
    /**
     * @var ResourceMagazine
     */
    protected $resource;

    /**
     * @var MagazineFactory
     */
    protected $magazineFactory;

    /**
     * @var MagazineCollectionFactory
     */
    protected $magazineCollectionFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var MagazineInterfaceFactory
     */
    protected $dataMagazineFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param ResourceMagazine $resource
     * @param MagazineFactory $magazineFactory
     * @param MagazineInterfaceFactory $dataMagazineFactory
     * @param MagazineCollectionFactory $magazineCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceMagazine $resource,
        MagazineFactory $magazineFactory,
        MagazineInterfaceFactory $dataMagazineFactory,
        MagazineCollectionFactory $magazineCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resource = $resource;
        $this->magazineFactory = $magazineFactory;
        $this->magazineCollectionFactory = $magazineCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataMagazineFactory = $dataMagazineFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save block data
     *
     * @param MagazineInterface $magazine
     * @return MagazineInterface
     * @throws CouldNotSaveException
     */
    public function save(MagazineInterface $magazine)
    {
        try {
            $this->resource->save($magazine);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $magazine;
    }

    /**
     * Load block data by given identity
     * @param $magazineId
     * @return Magazine|mixed
     */
    public function getById($magazineId)
    {
        /**
         * @var MagazineAlias $magazine
         */
        $magazine = $this->magazineFactory->create();
        $this->resource->load($magazine, $magazineId);
        return $magazine;
    }

    /**
     * Delete Block
     * @param MagazineInterface $magazine
     * @return bool|mixed
     */
    public function delete(MagazineInterface $magazine)
    {
        try {
            $this->resource->delete($magazine);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Magazine by given Block Identity
     *
     * @param string $magazineId
     * @return bool
     */
    public function deleteById($magazineId)
    {
        return $this->delete($this->getById($magazineId));
    }

    /**
     * For first banner
     * @return DataObjectAlias|null
     */
    public function getFirstBanner()
    {
        $magazine = $this->magazineCollectionFactory->create();
        $magazine = $magazine->addFieldToFilter('type', 1)
            ->setOrder(
                'sort_order',
                'ASC'
            )->getFirstItem();
        if ($magazine && $magazine->getId()) {
            return $magazine;
        } else {
            return null;
        }
    }
}
