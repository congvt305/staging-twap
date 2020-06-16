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

use Eguana\Magazine\Api\MagazineRepositoryInterface;
use Eguana\Magazine\Api\Data;
use Eguana\Magazine\Model\ResourceModel\Magazine as ResourceMagazine;
use Eguana\Magazine\Model\ResourceModel\Magazine\CollectionFactory as MagazineCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var \Eguana\Magazine\Api\Data\MagazineInterfaceFactory
     */
    protected $dataMagazineFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param ResourceMagazine $resource
     * @param MagazineFactory $magazineFactory
     * @param Data\MagazineInterfaceFactory $dataMagazineFactory
     * @param MagazineCollectionFactory $magazineCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceMagazine $resource,
        MagazineFactory $magazineFactory,
        \Eguana\Magazine\Api\Data\MagazineInterfaceFactory $dataMagazineFactory,
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
     * Save Block data
     *
     * @param \Eguana\Magazine\Api\Data\MagazineInterface $magazine
     * @return Magazine
     * @throws CouldNotSaveException
     */
    public function save(Data\MagazineInterface $magazine)
    {
        if (empty($magazine->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $magazine->setStoreId($storeId);
        }

        try {
            $this->resource->save($magazine);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $magazine;
    }

    /**
     * Load Block data by given Block Identity
     *
     * @param string $magazineId
     * @return Magazine
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($magazineId)
    {
        /**
         * @var \Eguana\Magazine\Model\Magazine $magazine
         */
        $magazine = $this->magazineFactory->create();
        $this->resource->load($magazine, $magazineId);
        if (!$magazine->getId()) {
            throw new NoSuchEntityException(__('Magazine with id "%1" does not exist.', $magazineId));
        }
        return $magazine;
    }

    /**
     * Delete Block
     *
     * @param \Eguana\Magazine\Api\Data\MagazineInterface $magazine
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\MagazineInterface $magazine)
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
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($magazineId)
    {
        return $this->delete($this->getById($magazineId));
    }
}
