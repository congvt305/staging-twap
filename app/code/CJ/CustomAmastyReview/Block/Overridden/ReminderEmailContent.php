<?php

namespace CJ\CustomAmastyReview\Block\Overridden;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Filter\Input\MaliciousCode;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Class ReminderEmailContent
 */
class ReminderEmailContent extends \Amasty\AdvancedReview\Block\Email\ReminderEmailContent
{
    /**
     * @var int
     */
    private $storeId;

    /**
     * @var array
     */
    private $productIds;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var \Magento\Framework\Url
     */
    private $url;

    /**
     * @var MaliciousCode
     */
    private $maliciousCode;

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Template\Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param UrlFinderInterface $urlFinder
     * @param \Magento\Framework\Url $url
     * @param MaliciousCode $maliciousCode
     * @param Image $imageHelper
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Template\Context           $context,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder      $searchCriteriaBuilder,
        UrlFinderInterface         $urlFinder,
        \Magento\Framework\Url     $url,
        MaliciousCode              $maliciousCode,
        Image                      $imageHelper,
        StoreManagerInterface $storeManager,
        array                      $data = []
    )
    {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->urlFinder = $urlFinder;
        $this->url = $url;
        $this->maliciousCode = $maliciousCode;
        $this->imageHelper = $imageHelper;
        $this->storeManager = $storeManager;
        parent::__construct($context, $productRepository, $searchCriteriaBuilder, $urlFinder, $url, $maliciousCode, $imageHelper, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function getProducts()
    {
        $ids = $this->getProductIds();
        $ids = array_unique($ids);
        $websiteId = $this->storeManager->getWebsite()->getId();
        $this->searchCriteriaBuilder->addFilter('entity_id', $ids, 'in');
        $this->searchCriteriaBuilder->addFilter('visibility', 4);
        $this->searchCriteriaBuilder->addFilter('website_id', $websiteId);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $products = $this->productRepository->getList($searchCriteria)->getItems();

        return $products;
    }

    /**
     * Override to custom get url product
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return string
     */
    public function getProductUrl(\Magento\Catalog\Model\Product $product)
    {
        $params = [];
        $params['_nosid'] = true;
        $routePath = '';
        $routeParams = $params;

        $storeId = $product->getStoreId();

        $categoryId = null;

        if ($product->hasUrlDataObject()) {
            $requestPath = $product->getUrlDataObject()->getUrlRewrite();
            $routeParams['_scope'] = $product->getUrlDataObject()->getStoreId();
        } else {
            $requestPath = $product->getRequestPath();
            if (empty($requestPath) && $requestPath !== false) {
                $filterData = [
                    UrlRewrite::ENTITY_ID => $product->getId(),
                    UrlRewrite::ENTITY_TYPE => \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::ENTITY_TYPE,
                    UrlRewrite::STORE_ID => $storeId,
                ];
                if ($categoryId) {
                    $filterData[UrlRewrite::METADATA]['category_id'] = $categoryId;
                }
                $rewrite = $this->urlFinder->findOneByData($filterData);
                if ($rewrite) {
                    $requestPath = $rewrite->getRequestPath();
                    $product->setRequestPath($requestPath);
                } else {
                    $product->setRequestPath(false);
                }
            }
        }

        if (!empty($requestPath)) {
            $routeParams['_direct'] = $requestPath;
        } else {
            $routePath = 'catalog/product/view';
            $routeParams['id'] = $product->getId();
            $routeParams['s'] = $product->getUrlKey();
            if ($categoryId) {
                $routeParams['category'] = $categoryId;
            }
        }

        // reset cached URL instance GET query params
        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = [];
        }
        //custom here
        //set store for route param to avoid get the same cache key for same product, same url but diffirent site when get url in send multiple email at the same time
        $routeParams['storeId'] = $storeId;
        //end custom
        return $this->url->setScope($storeId)->getUrl($routePath, $routeParams);
    }
}
