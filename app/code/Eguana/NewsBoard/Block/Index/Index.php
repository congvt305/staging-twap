<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 15/9/20
 * Time: 4:46 PM
 */
declare(strict_types=1);

namespace Eguana\NewsBoard\Block\Index;

use Eguana\NewsBoard\Model\ResourceModel\News\CollectionFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Eguana\NewsBoard\Model\NewsConfiguration\NewsConfiguration;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Pager;
use Psr\Log\LoggerInterface;

/**
 * Class for listing of news
 *
 * Class Index
 */
class Index extends Template
{
    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var NewsConfiguration
     */
    private $newsConfiguration;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var CollectionFactory
     */
    private $newsCollection;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param DateTime $date
     * @param CollectionFactory $newsCollection
     * @param StoreManagerInterface $storeManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     * @param SortOrderBuilder $sortOrderBuilder
     * @param NewsConfiguration $newsConfiguration
     */
    public function __construct(
        Context $context,
        DateTime $date,
        CollectionFactory $newsCollection,
        StoreManagerInterface $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger,
        SortOrderBuilder $sortOrderBuilder,
        NewsConfiguration $newsConfiguration
    ) {
        $this->date = $date;
        $this->newsCollection = $newsCollection;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->newsConfiguration = $newsConfiguration;
        parent::__construct($context);
    }

    /**
     * prepare layout
     *
     * @return $this|Index
     */
    protected function _prepareLayout()
    {
        try {
            parent::_prepareLayout();
            if ($this->getNewsCollection()) {
                $this->pageConfig->getTitle()->set(__('News'));
                $pager = $this->getLayout()->createBlock(
                    Pager::class,
                    'custom.history.pager'
                )->setAvailableLimit($this->getpagenationArray())
                    ->setShowPerPage(true)->setCollection(
                        $this->getNewsCollection()
                    );
                $this->setChild('pager', $pager);
                $this->getNewsCollection()->load();
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        parent::_prepareLayout();
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml() : string
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get collection of news using repository
     *
     * @return mixed
     */
    public function getNewsCollection()
    {
        $collection = [];
        try {
            $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
            $news = $this->getPerPageNews();
            $pageSize  = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : $news;
            $currentStoreId = $this->storeManager->getStore()->getId();
            $collection = $this->newsCollection->create();
            $collection->addFieldToFilter('is_active', ['eq' => '1']);
            $collection->addFieldToFilter('date', ['lteq' => $this->date->gmtDate()]);
            $collection->addStoreFilter($currentStoreId);
            $collection->setOrder('date', $this->getSortOrder());
            $collection->setPageSize($pageSize);
            $collection->setCurPage($page);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $collection;
    }

    /**
     * get sort oredr of news from configuration
     *
     * @return string
     */
    private function getSortOrder()
    {
        return $this->newsConfiguration->getConfigValue('sort_order');
    }

    /**
     * get no of recorde show on per page
     *
     * @return int
     */
    public function getPerPageNews()
    {
        return $this->newsConfiguration->getConfigValue('per_page_news');
    }

    /**
     * get array of pagination
     *
     * @return array
     */
    public function getpagenationArray()
    {
        $news = $this->getPerPageNews();
        $result = [];
        for ($i = 1; $i < 5; $i++) {
            $result[$news * $i] = $news * $i;
        }
        return $result;
    }

    /**
     * get date of news
     *
     * @param $dateTime
     * @return string
     */
    public function getNewsDate($dateTime)
    {
        return $this->date->date('Y.m.d', strtotime($dateTime));
    }

    /**
     * Get url of News ddetail page
     *
     * @param $identifier
     * @return string
     */
    public function getNewsDetailUrl($identifier) : string
    {
        $Url = '';
        try {
            $Url = $this->storeManager->getStore()->getBaseUrl();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $Url . $identifier;
    }

    /**
     * get image url
     *
     * @param $url
     * @return string
     */
    public function getImageUrl($url)
    {
        $mediaUrl = '';
        try {
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $mediaUrl . $url;
    }

    /**
     * get category name
     *
     * @param array $categoriesId
     * @return mixed|string
     */
    public function getCategoryName(array $categoriesId)
    {
        $category = '';
        try {
            $storeId = $this->storeManager->getStore()->getId();
            foreach ($categoriesId as $value) {
                $categoryStoreId = explode('.', $value);
                if ($storeId == $categoryStoreId[0]) {
                    $categories = $this->newsConfiguration->getCategory('category', $categoryStoreId[0]);
                    if (isset($categories[$categoryStoreId[1]])) {
                        $category = $categories[$categoryStoreId[1]];
                    } else {
                        $category = '';
                    }
                    break;
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $category;
    }
}
