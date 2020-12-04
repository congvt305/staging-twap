<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/10/20
 * Time: 06:48 PM
 */

namespace Eguana\NewsBoard\ViewModel;

use Eguana\NewsBoard\Api\NewsRepositoryInterface;
use Eguana\NewsBoard\Model\NewsConfiguration\NewsConfiguration;
use Eguana\NewsBoard\Model\ResourceModel\News\CollectionFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Eguana\NewsBoard\Api\Data\NewsInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Detail
 *  Eguana\NewsBoard\ViewModel
 */
class Detail implements ArgumentInterface
{

    /**
     * @var CollectionFactory
     */
    private $newsCollection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var NewsRepositoryInterface
     */
    private $newsRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var NewsConfiguration
     */
    private $newsConfiguration;

    /**
     * Detail constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param FilterProvider $filterProvider
     * @param CollectionFactory $newsCollection
     * @param RedirectFactory $redirectFactory
     * @param NewsRepositoryInterface $newsRepository
     * @param DateTime $date
     * @param RequestInterface $request
     * @param NewsConfiguration $newsConfiguration
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        FilterProvider $filterProvider,
        CollectionFactory $newsCollection,
        RedirectFactory $redirectFactory,
        NewsRepositoryInterface $newsRepository,
        DateTime $date,
        RequestInterface $request,
        NewsConfiguration $newsConfiguration,
        LoggerInterface $logger
    ) {
        $this->date = $date;
        $this->newsConfiguration = $newsConfiguration;
        $this->newsCollection = $newsCollection;
        $this->storeManager = $storeManager;
        $this->redirectFactory = $redirectFactory;
        $this->newsRepository = $newsRepository;
        $this->logger = $logger;
        $this->request = $request;
        $this->filterProvider=$filterProvider;
        $this->logger = $logger;
    }

    /**
     * get Detail of news from repository using getById method
     *
     * @return NewsInterface|string
     */
    public function getNewsDetail()
    {
        $news = '';
        try {
            $news = $this->newsRepository->getById($this->request->getParam('news_id'));
            $store_id = $this->storeManager->getStore()->getId();
            if (in_array($store_id, $news['store_id']) || in_array('0', $news['store_id']) && $news['is_active'] == 1) {
                return $news;
            } else {
                $news = '';
                return $news;
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
            return $news;
        }
        return $news;
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
            $currentStoreId = $this->storeManager->getStore()->getId();
            $collection = $this->newsCollection->create();
            $collection->addFieldToFilter('is_active', ['eq' => '1']);
            $collection->addFieldToFilter('date', ['lteq' => $this->date->gmtDate()]);
            $collection->addFieldToFilter('news_id', ['neq' => $this->request->getParam('news_id')]);
            $collection->addStoreFilter($currentStoreId);
            $collection->setPageSize(4);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $collection;
    }

    /**
     * get date from date time
     *
     * @param $dateTime
     * @return string
     */
    public function getNewsDate($dateTime)
    {
        return $this->date->date('Y.m.d', strtotime($dateTime));
    }

    /**
     * get filter provider
     *
     * @param $newsdescription
     * @return FilterProvider
     */
    public function getNewsDiscription($newsdescription)
    {
        $description = '';
        try {
            return $this->filterProvider->getPageFilter()->filter($newsdescription);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $description;
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
     * get base url of website
     *
     * @return string
     */
    public function getDetailPageUrl() : string
    {
        $url = '';
        try {
            $url= $this->storeManager->getStore()->getBaseUrl();
            return $url;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $url;
    }

    /**
     * Get news Id
     *
     * @param $news_id
     * @return string
     */
    public function getnewsId($news_id) : string
    {
        return 'news/index/detail/news_id/'. $news_id;
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
