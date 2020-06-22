<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 16/6/20
 * Time: 12:53 PM
 */

namespace Eguana\VideoBoard\Block;

use Eguana\VideoBoard\Model\VideoBoard;
use Magento\Framework\View\Element\Template;
use Magento\Framework\UrlInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CountryFactory;
use Eguana\VideoBoard\Model\ResourceModel\VideoBoard\CollectionFactory;
use Eguana\VideoBoard\Api\VideoBoardRepositoryInterface;
use Eguana\VideoBoard\Helper\Data;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Abstract class for blocks
 *
 * Class AbstractBlock
 */

class AbstractBlock extends Template implements IdentityInterface
{
    /**
     * @var Data
     */
    private $helperData;
    /**
     * @var CollectionFactory
     */
    private $videoBoardCollectionFactory;
    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @var FilterProvider
     */
    public $filterProvider;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var VideoBoardRepositoryInterface
     */
    public $videoBoardRepository;

    /**
     * VideoBoard constructor.
     * @param Template\Context $context
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlInterface
     * @param FilterProvider $filterProvider
     * @param StoreManagerInterface $storeManager
     * @param VideoBoardRepositoryInterface $videoBoardRepository
     * @param CountryFactory $countryFactory
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $collectionFactory,
        UrlInterface $urlInterface,
        FilterProvider $filterProvider,
        StoreManagerInterface $storeManager,
        VideoBoardRepositoryInterface $videoBoardRepository,
        CountryFactory $countryFactory,
        Data $helperData,
        array $data = []
    ) {
        $this->videoBoardCollectionFactory = $collectionFactory;
        $this->urlInterface = $urlInterface;
        $this->filterProvider = $filterProvider;
        $this->storeManager = $storeManager;
        $this->videoBoardRepository = $videoBoardRepository;
        $this->countryFactory = $countryFactory;
        $this->helperData= $helperData;

        parent::__construct($context, $data);
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
            return $this->_storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
            $this->_logger->debug($exception->getMessage());
        }
    }

    /**
     * Get VideoBoard collection
     *
     * @return mixed
     */
    public function getCollection()
    {
        if (!$this->getData('collection')) {
            /** @var \Eguana\VideoBoard\Model\ResourceModel\VideoBoard\Collection $videoBoardCollection */
            $videoBoardCollection = $this->videoBoardCollectionFactory->create();
            $storeId = $this->storeManager->getStore()->getId();

            $sortDirection = 'asc';
            if ($this->helperData->getConfig('videoboard/general/sort_direction') == 1) {
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
            );
            $videoBoardCollection->setPageSize(6);
            $this->setCollection($videoBoardCollection);
        }
        return $this->getData('collection');
    }

    /**
     * To filter the content
     * This function will get the content, specially the page builder content and make it renderable at frontend
     * @param $content
     * @return mixed
     */
    public function contentFiltering($content)
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
            $this->_logger->debug($exception->getMessage());
        }
        return $this->filterProvider->getBlockFilter()->setStoreId($storeId)
            ->filter($content);
    }

    /**
     * To get relative url
     * This function will return the full relative url for VideoBoard
     * @param $urlkey
     * @return string
     */
    public function getVideoBoardUrl($urlkey)
    {
        return $this->getUrl() . 'videoboard/details/index/id/' . $urlkey;
    }
}
