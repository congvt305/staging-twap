<?php

namespace CJ\CustomAmastyReview\Block\Overridden;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Filter\Input\MaliciousCode;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;

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
}
