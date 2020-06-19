<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 11/6/20
 * Time: 11:47 AM
 */

namespace Eguana\VideoBoard\Model;

use Eguana\VideoBoard\Api\VideoBoardRepositoryInterface;
use Eguana\VideoBoard\Api\Data;
use Eguana\VideoBoard\Model\ResourceModel\VideoBoard as ResourceVideoBoard;
use Eguana\VideoBoard\Model\ResourceModel\VideoBoard\CollectionFactory as VideoBoardCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Eguana\VideoBoard\Api\Data\VideoBoardInterfaceFactory;
use Eguana\VideoBoard\Model\VideoBoard;

/**
 * Class VideoBoardRepository
 * Eguana\VideoBoard\Model
 */
class VideoBoardRepository implements VideoBoardRepositoryInterface
{
    /**
     * @var ResourceVideoBoard
     */
    private $resource;

    /**
     * @var VideoBoardFactory
     */
    private $videoBoardFactory;

    /**
     * @var VideoBoardCollectionFactory
     */
    private $videoBoardCollectionFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var VideoBoardInterfaceFactory
     */
    private $dataVideoBoardFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param ResourceVideoBoard $resource
     * @param VideoBoardFactory $videoBoardFactory
     * @param Data\VideoBoardInterfaceFactory $dataVideoBoardFactory
     * @param VideoBoardCollectionFactory $videoBoardCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceVideoBoard $resource,
        VideoBoardFactory $videoBoardFactory,
        VideoBoardInterfaceFactory $dataVideoBoardFactory,
        VideoBoardCollectionFactory $videoBoardCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resource = $resource;
        $this->videoBoardFactory = $videoBoardFactory;
        $this->videoBoardCollectionFactory = $videoBoardCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataVideoBoardFactory = $dataVideoBoardFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save Block data
     *
     * @param VideoBoardInterfaceFactory $videoBoard
     * @return VideoBoard
     * @throws CouldNotSaveException
     */
    public function save(Data\VideoBoardInterface $videoBoard)
    {
        if (empty($videoBoard->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $videoBoard->setStoreId($storeId);
        }

        try {
            $this->resource->save($videoBoard);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $videoBoard;
    }

    /**
     * Load Block data by given Block Identity
     *
     * @param string $videoBoardId
     * @return VideoBoard
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($videoBoardId)
    {
        /**
         * @var VideoBoard $videoBoard
         */
        $videoBoard = $this->videoBoardFactory->create();
        $this->resource->load($videoBoard, $videoBoardId);
        if (!$videoBoard->getId()) {
            throw new NoSuchEntityException(__('Video with id "%1" does not exist.', $videoBoardId));
        }
        return $videoBoard;
    }

    /**
     * Delete Block
     *
     * @param VideoBoardInterface $videoBoard
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\VideoBoardInterface $videoBoard)
    {
        try {
            $this->resource->delete($videoBoard);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete VideoBoard by given Block Identity
     *
     * @param string $VideoBoardId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($videoBoardId)
    {
        return $this->delete($this->getById($videoBoardId));
    }
}
