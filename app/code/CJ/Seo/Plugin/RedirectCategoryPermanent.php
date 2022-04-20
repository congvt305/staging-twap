<?php

namespace CJ\Seo\Plugin;

use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Controller\Category\View;
use Psr\Log\LoggerInterface as Logger;
use CJ\Seo\Helper\Data as DataHelper;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class RedirectCategoryPermanent
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
     * @var CategoryRepositoryInterface
     */
    protected CategoryRepositoryInterface $categoryRepository;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var DataHelper
     */
    protected DataHelper $dataHelper;

    /**
     * @var CategoryHelper
     */
    protected CategoryHelper $categoryHelper;

    /**
     * @param Logger $logger
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param RedirectFactory $redirectFactory
     * @param DataHelper $dataHelper
     * @param CategoryHelper $categoryHelper
     */
    public function __construct(
        Logger $logger,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        RedirectFactory $redirectFactory,
        DataHelper $dataHelper,
        CategoryHelper $categoryHelper
    ) {
        $this->logger = $logger;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->redirectFactory = $redirectFactory;
        $this->dataHelper = $dataHelper;
        $this->categoryHelper = $categoryHelper;
    }

    /**
     * @param View $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function aroundExecute(View $subject, callable $proceed)
    {
        try {
            $category = $this->getCategory($subject);
            if ($this->dataHelper->isAutomaticRedirectEnabled() && !$this->categoryHelper->canShow($category)) {
                $redirect = $this->redirectFactory->create();
                $redirect->setPath($this->storeManager->getStore()->getBaseUrl());
                $redirect->setHttpResponseCode(301);
                return $redirect;
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return $proceed();
    }

    /**
     * @param $subject
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCategory($subject)
    {
        $categoryId = $subject->getRequest()->getParam('page_id') ?? $subject->getRequest()->getParam('id');
        return $this->categoryRepository->get($categoryId, $this->storeManager->getStore()->getId());
    }
}
