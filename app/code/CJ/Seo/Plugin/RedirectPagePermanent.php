<?php

namespace CJ\Seo\Plugin;

use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Cms\Controller\Page\View;
use Psr\Log\LoggerInterface as Logger;
use CJ\Seo\Helper\Data as DataHelper;
use Magento\Cms\Api\PageRepositoryInterface;

class RedirectPagePermanent
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
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var DataHelper
     */
    protected DataHelper $dataHelper;

    /**
     * @var PageRepositoryInterface
     */
    protected PageRepositoryInterface $pageRepository;

    /**
     * @param Logger $logger
     * @param StoreManagerInterface $storeManager
     * @param RedirectFactory $redirectFactory
     * @param DataHelper $dataHelper
     * @param PageRepositoryInterface $pageRepository
     */
    public function __construct(
        Logger $logger,
        StoreManagerInterface $storeManager,
        RedirectFactory $redirectFactory,
        DataHelper $dataHelper,
        PageRepositoryInterface $pageRepository
    ) {
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->redirectFactory = $redirectFactory;
        $this->dataHelper = $dataHelper;
        $this->pageRepository = $pageRepository;
    }

    /**
     * @param View $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function aroundExecute(View $subject, callable $proceed)
    {
        try {
            $cmsPage = $this->getCmsPage($subject);
            if ($this->dataHelper->isAutomaticRedirectEnabled() && !$cmsPage->getIsActive()) {
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
     * @return \Magento\Cms\Api\Data\PageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCmsPage($subject)
    {
        $pageId = $subject->getRequest()->getParam('page_id') ?? $subject->getRequest()->getParam('id');
        return $this->pageRepository->getById($pageId);
    }
}
