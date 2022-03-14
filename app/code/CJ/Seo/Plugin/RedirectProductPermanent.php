<?php

namespace CJ\Seo\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Controller\Product\View;
use Psr\Log\LoggerInterface as Logger;
use Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;

class RedirectProductPermanent
{
    const IS_AUTOMATIC_REDIRECT = 'catalog/seo/is_automatic_redirect';

    /**
     * @var RedirectFactory
     */
    protected RedirectFactory $redirectFactory;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Logger $logger
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param RedirectFactory $redirectFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Logger $logger,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        RedirectFactory $redirectFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->redirectFactory = $redirectFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param View $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function aroundExecute(View $subject, callable $proceed)
    {
        try {
            $product = $this->getProduct($subject);
            if ($this->isAutomaticRedirectEnabled() && !$this->canShow($product)) {
                $redirect = $this->redirectFactory->create();
                if (str_contains($product->getRedirectUrl(), 'http')) {
                    $redirect->setPath($product->getRedirectUrl());
                } else if ($product->getRedirectUrl()) {
                    $redirect->setPath($this->storeManager->getStore()->getBaseUrl() . $product->getRedirectUrl() . $this->getSuffixProduct());
                } else {
                    $redirect->setPath($this->storeManager->getStore()->getBaseUrl());
                }
                $redirect->setHttpResponseCode(301);
                return $redirect;
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return $proceed();
    }

    /**
     * @param $product
     * @return bool
     */
    protected function canShow($product)
    {
        return $product->isVisibleInCatalog() && $product->isVisibleInSiteVisibility();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getSuffixProduct()
    {
        return $this->scopeConfig->getValue(
            ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function isAutomaticRedirectEnabled()
    {
        return $this->scopeConfig->getValue(
            self::IS_AUTOMATIC_REDIRECT,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * @param $subject
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getProduct($subject)
    {
        $productId = (int) $subject->getRequest()->getParam('id');
        return $this->productRepository->getById($productId, false, $this->storeManager->getStore()->getId());
    }
}
