<?php

namespace CJ\CatalogProduct\Controller\Index;

use CJ\CatalogProduct\Helper\Data as HelperData;
use CJ\CatalogProduct\Helper\Logger;
use CJ\CatalogProduct\Model\Source\Config\ProductSalesSource;
use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Review\Model\Review;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class BestSeller
 * @package CJ\CatalogProduct\Controller\Index
 */
class BestSeller extends Action implements HttpGetActionInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var HelperData
     */
    protected $helperData;
    /**
     * @var CurrencyFactory
     */
    protected $currencyFactory;
    /**
     * @var Review
     */
    protected $review;

    /**
     * BestSeller constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param JsonFactory $resultJsonFactory
     * @param CollectionFactory $collectionFactory
     * @param Logger $logger
     * @param HelperData $helperData
     * @param CurrencyFactory $currencyFactory
     * @param Review $review
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        JsonFactory $resultJsonFactory,
        CollectionFactory $collectionFactory,
        Logger $logger,
        HelperData $helperData,
        CurrencyFactory $currencyFactory,
        Review $review
    )
    {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->currencyFactory = $currencyFactory;
        $this->review = $review;
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $store = $this->storeManager->getStore();
        $storeId = $store->getId();
        if (!$storeId) {
            $storeId = $this->getRequest()->getParam('store_id');
        }
        $products['data'] = [];
        if ($storeId) {
            try {
                $limitProduct = $this->helperData->getLimitProducts($storeId);
                $useProductSale = $this->helperData->getDataUseProductSale($storeId);

                $productCollection = $this->collectionFactory->create()
                    ->addStoreFilter($storeId)
                    ->addAttributeToSelect($this->getProductAttributes())
                    ->addFieldToFilter(HelperData::RANKING_ATTRIBUTE_CODE, ['gteq' => 1])
                    ->addFinalPrice()
                    ->setOrder(HelperData::RANKING_ATTRIBUTE_CODE, 'ASC')
                    ->setPageSize($limitProduct);

                if ($useProductSale == ProductSalesSource::USE_ATTRIBUTE_ON_SALE) {
                    $productCollection->addFieldToFilter(HelperData::ON_SALES_ATTRIBUTE_CODE, 1);
                } elseif ($useProductSale == ProductSalesSource::AUTOMATIC) {
                    $productCollection->getSelect()->where('price_index.final_price < price_index.price');
                }

                if ($pageSize = $productCollection->getSize()) {
                    $currencyCode = $store->getCurrentCurrencyCode();
                    $currency = $this->currencyFactory->create()->load($currencyCode);
                    $currencySymbol = $currency->getCurrencySymbol();

                    foreach ($productCollection as $product) {
                        /**
                         * @var Product $product
                         */
                        $this->review->getEntitySummary($product, $storeId);

                        if ($reviewRate = $product->getRatingSummary()->getRatingSummary()) {
                            $reviewRate = $reviewRate * 5 / 100;
                        }
                        $products['data'][] = [
                            'page_size' => $pageSize,
                            'name' => $product->getName(),
                            'entity_id' => $product->getId(),
                            'pirce' => $product->getPrice(),
                            'finalprice' => $product->getFinalPrice(),
                            'symbol' => $currencySymbol,
                            'review_rate' => $reviewRate ?? 0,
                            'review_total' => $product->getRatingSummary()->getReviewsCount() ?? 0,
                            'ranking' => $product->getData(HelperData::RANKING_ATTRIBUTE_CODE),
                            'ranking_status' => $product->getData(HelperData::RANKING_STATUS_ATTRIBUTE_CODE)
                        ];
                    }
                }
            } catch (Exception $exception) {
                $this->messageManager->addErrorMessage(__('Something went wrong with block best seller!'));
                $this->logger->logException($exception, __("Best Seller"));
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($products);
    }

    /**
     * @return array
     */
    public function getProductAttributes()
    {
        return [ProductInterface::NAME, HelperData::ON_SALES_ATTRIBUTE_CODE, HelperData::RANKING_ATTRIBUTE_CODE, HelperData::RANKING_STATUS_ATTRIBUTE_CODE];
    }
}