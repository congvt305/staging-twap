<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 23/6/20
 * Time: 5:08 PM
 */
namespace Eguana\VideoBoard\ViewModel;

use Eguana\VideoBoard\Model\ResourceModel\VideoBoard\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Eguana\VideoBoard\Api\VideoBoardRepositoryInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Eguana\VideoBoard\Helper\Data;
use Magento\Framework\App\RequestInterface;

/**
 * This class used to add breadcrumbs and title
 *
 * Class VideoList
 * Eguana\VideoBoard\Block
 */
class VideoList implements ArgumentInterface
{
    /**
     * Constant
     */
    const XML_PATH_VIDEO_SORT_ORDER = 'videoboard/general/sort_direction';

    const DEAFULT_VIDEO_COUNT = 6;
    /**
     * @var CollectionFactory
     */
    private $videoBoardCollectionFactory;
    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var VideoBoardRepositoryInterface
     */
    public $videoBoardRepository;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

    /**
     * VideoBoard constructor.
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlInterface
     * @param StoreManagerInterface $storeManager
     * @param VideoBoardRepositoryInterface $videoBoardRepository
     * @param RequestInterface $requestInterface
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        UrlInterface $urlInterface,
        VideoBoardRepositoryInterface $videoBoardRepository,
        StoreManagerInterface $storeManager,
        RequestInterface $requestInterface,
        Data $helperData,
        array $data = []
    ) {
        $this->videoBoardCollectionFactory = $collectionFactory;
        $this->urlInterface = $urlInterface;
        $this->storeManager = $storeManager;
        $this->videoBoardRepository = $videoBoardRepository;
        $this->helperData= $helperData;
        $this->requestInterface = $requestInterface;
    }
    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [VideoBoard::CACHE_TAG];
    }

    /**
     * get store id
     * @return int
     */
    public function getStoreId()
    {
        try {
            return $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
            $this->_logger->debug($exception->getMessage());
        }
    }
    /**
     * Get VideoBoard collection
     *
     * @return mixed
     */
    public function getVideoCollection()
    {
        $param = $this->requestInterface->getParam('count');
        $count = self::DEAFULT_VIDEO_COUNT;
        if (isset($param)) {
            $count = $param * 6;
        }
        $videoBoardCollection = $this->videoBoardCollectionFactory->create();
        $storeId = $this->storeManager->getStore()->getId();

        $sortDirection = 'asc';
        if ($this->helperData->getConfig(self::XML_PATH_VIDEO_SORT_ORDER) == 1) {
                $sortDirection = 'desc';
        }
        $videoBoardCollection->addFieldToFilter(
            "is_active",
            ["eq" => true]
        )->addFieldToFilter(
            ['store_id','store_id','store_id','store_id'],
            [["like" =>  '%' . $storeId . ',%'],
                ["like" =>  '%,' . $storeId . ',%'],
                ["like" =>  '%,' . $storeId . '%'],
                ["eq" => $storeId]]
        )->setOrder(
            "entity_id",
            $sortDirection
        );
        $videoBoardCollection->setPageSize($count);

        return $videoBoardCollection;
    }

    /**
     * To get relative url
     * This function will return the full relative url for VideoBoard
     * @param $urlkey
     * @return string
     */
    public function getVideoBoardUrl($urlkey)
    {
        return $this->urlInterface->getUrl() . 'videoboard/detail/index/id/' . $urlkey;
    }
}
