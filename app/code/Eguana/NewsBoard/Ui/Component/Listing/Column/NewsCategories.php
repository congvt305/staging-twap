<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/11/20
 * Time: 4:50 PM
 */
namespace Eguana\NewsBoard\Ui\Component\Listing\Column;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterfaceAlias;
use Magento\Framework\App\RequestInterface;
use Eguana\NewsBoard\Api\NewsRepositoryInterface;
use Magento\Store\Ui\Component\Listing\Column\Store\Options;
use Eguana\NewsBoard\Model\NewsConfiguration\NewsConfiguration;
use Psr\Log\LoggerInterface;

/**
 * This class is used to add show the available categories of news
 *
 * Class NewsCategories
 */
class NewsCategories extends Options
{
    /**
     * @var NewsConfiguration
     */
    private $newsConfiguration;

    /**
     * @var NewsRepositoryInterface
     */
    private $newsRepository;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var StoreManagerInterfaceAlias
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * NewsCategories constructor.
     *
     * @param Json $json
     * @param NewsRepositoryInterface $newsRepository
     * @param RequestInterface $request
     * @param NewsConfiguration $newsConfiguration
     * @param StoreManagerInterfaceAlias $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Json $json,
        NewsRepositoryInterface $newsRepository,
        RequestInterface $request,
        NewsConfiguration $newsConfiguration,
        StoreManagerInterfaceAlias $storeManager,
        LoggerInterface $logger
    ) {
        $this->newsConfiguration = $newsConfiguration;
        $this->json = $json;
        $this->logger = $logger;
        $this->newsRepository = $newsRepository;
        $this->request = $request;
        $this->storeManager = $storeManager;
    }

    /**
     * get categories options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $storeCategoryList = [];
        try {
            $isStoreIdZero = false;
            $id = $this->request->getParam('news_id');
            if (isset($id)) {
                $news = $this->newsRepository->getById($this->request->getParam('news_id'));
                if ($news['store_id'][0] == 0) {
                    $isStoreIdZero = true;
                }
            }
            $categoryList = [];
            $storeId[] = [];
            $param = 0;
            if (!isset($news) || $isStoreIdZero) {
                $storeManagerDataList = $this->storeManager->getStores();
                foreach ($storeManagerDataList as $key => $value) {
                    $storeId[] = $key;
                }
            } else {
                $storeId = $news['store_id'];
                $param = 1;
            }
            $i = 0;
            $index = 0;
            foreach ($storeId as $key) {
                if ($i == 0 && $param == 0) {
                    $i++;
                    continue;
                } else {
                    $id = $key;
                }
                $result = $this->newsConfiguration->getCategory('category', $id);
                $categoryId = 0;
                if (count($result) > 0) {
                    foreach ($result as $category) {
                        $categoryList[$index][] = [
                            'label' => $category,
                            'value' => $id . '.' . $categoryId
                        ];
                        $categoryId++;
                    }
                    $storeCategoryList[$index] = [
                        'label'   => $this->storeManager->getStore($id)->getName(),
                        'value'   => $categoryList[$index]
                    ];
                    $index++;
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $storeCategoryList;
    }

    /**
     * get categories of news fron configuration
     *
     * @return array
     */
    public function getCategories()
    {
        $result = $this->newsConfiguration->getConfigValue('category');
        $category = [];
        if (isset($result)) {
            $categories = $this->json->unserialize($result);
            foreach ($categories as $value) {
                $category[$value['attribute_name']] = $value['attribute_name'];
            }
        }
        return $category;
    }
}
