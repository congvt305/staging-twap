<?php

namespace CJ\Seo\Plugin;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Controller\Product\View;
use Psr\Log\LoggerInterface as Logger;
use CJ\Seo\Helper\Data as DataHelper;
use Magento\Catalog\Helper\Category as CategoryHelper;

class RedirectProductPermanent
{


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
     * @var DataHelper
     */
    protected DataHelper $dataHelper;

    /**
     * @param Logger $logger
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param RedirectFactory $redirectFactory
     * @param DataHelper $dataHelper
     * @param CategoryHelper $categoryHelper
     */
    public function __construct(
        Logger $logger,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        RedirectFactory $redirectFactory,
        DataHelper $dataHelper
    ) {
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->redirectFactory = $redirectFactory;
        $this->dataHelper = $dataHelper;
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
            if ($this->dataHelper->isAutomaticRedirectEnabled() && !$this->canShow($product)) {
                $redirect = $this->redirectFactory->create();
                if (str_contains($product->getRedirectUrl(), 'http')) {
                    $redirect->setPath($product->getRedirectUrl());
                } else if ($product->getRedirectUrl()) {
                    $redirect->setPath($this->storeManager->getStore()->getBaseUrl() . $product->getRedirectUrl() . $this->dataHelper->getSuffixProduct());
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
