<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Helper;

use Eguana\Faq\Api\Data\FaqInterface;
use Eguana\Faq\Model\Faq as FaqModel;
use Eguana\Faq\Model\ResourceModel\Faq\Collection;
use Eguana\Faq\Model\ResourceModel\Faq\CollectionFactory;
use Eguana\Faq\Model\Source\FaqList;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * Eguana\Faq\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var CollectionFactory
     */
    private $faqCollectionFactory;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var Category
     */
    private $category;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param RequestInterface $request
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        RequestInterface $request
    ) {
        $this->faqCollectionFactory = $collectionFactory;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function getFaqEnabled()
    {
        return $this->scopeConfig->getValue('faq/general/enabled', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getFaqTypes()
    {
        return $this->scopeConfig->getValue(
            'faq/category',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get faq listings sort order
     * @return mixed
     */
    public function getFaqSortOrder()
    {
        return $this->scopeConfig->getValue(
            'faq/general/sort_order',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $storeCode
     * @return mixed
     */
    public function getStoreCategories($storeCode)
    {
        return $this->scopeConfig->getValue(
            'faq/category/categories',
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    /**
     * Get product
     *
     * @return Product|mixed|null
     */
    public function getProduct()
    {
        if (!$this->product) {
            $this->product = $this->registry->registry('current_product');
        }
        return $this->product;
    }

    /**
     * Get category
     *
     * @return Category|mixed|null
     */
    public function getCategory()
    {
        if (!$this->category) {
            $this->category = $this->registry->registry('current_category');
        }
        return $this->category;
    }

    /**
     * Get data for specific faq
     *
     * @param int $pageSize
     * @param int $curPage
     * @return false|\Magento\Framework\DataObject[]|string[]|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFaqData($pageSize = 0, $curPage = 0)
    {
        if ($this->isPdpPage()) {
            $product = $this->getProduct();
            $faq = ($product && $product->getFaq()) ? explode(',', $product->getFaq()) : null;
        } else {
            $category = $this->getCategory();
            $faq = ($category && $category->getFaq()) ? explode(',', $category->getFaq()) : null;
        }
        if ($faq) {
            /**
             * @var Collection $faqCollection
             * @var FaqModel $faq
             */
            $faqCollection = $this->faqCollectionFactory->create();
            $currentStoreId = $this->storeManager->getStore()->getId();
            $faqCollection->getSelect()->reset('columns')->columns(['title', 'description']);
            $faqCollection->addFieldToFilter(FaqInterface::IS_ACTIVE, ['eq' => true])
                ->addFieldToFilter(FaqList::IS_USE_IN_CATALOG_COLUMN, FaqList::USE_IN_CATALOG)
                ->addFieldToFilter(FaqInterface::ENTITY_ID, ['in' => $faq])
                ->addStoreFilter($currentStoreId);
            if ($pageSize && $curPage) {
                $faqCollection->setPageSize($pageSize)
                    ->setCurPage($curPage);
            }

            return $faqCollection->getItems();
        }

        return $faq;
    }

    /**
     * Get header for faq
     *
     * @return null
     */
    public function getHeaderFaq()
    {
        if ($this->isPdpPage()) {
            $product = $this->getProduct();
            $headerTitleFaq = ($product && $product->getHeaderTitleFaq()) ? $product->getHeaderTitleFaq() : null;
        } else {
            $category = $this->getCategory();
            $headerTitleFaq = ($category && $category->getHeaderTitleFaq()) ? $category->getHeaderTitleFaq() : null;
        }
        return $headerTitleFaq;
    }

    /**
     * IsPdpPage
     *
     * @return bool
     */
    public function isPdpPage()
    {
        return in_array($this->request->getFullActionName(), ['catalog_product_view']) || $this->request->getParam('product_id');
    }
}
