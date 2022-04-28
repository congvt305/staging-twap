<?php

namespace CJ\Seo\Plugin;

use Magento\Cms\Controller\Noroute\Index as NorouteIndex;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as Logger;
use CJ\Seo\Helper\Data as DataHelper;

class RedirectNoRoutePermanent
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
     * @param Logger $logger
     * @param StoreManagerInterface $storeManager
     * @param RedirectFactory $redirectFactory
     * @param DataHelper $dataHelper
     */
    public function __construct(
        Logger $logger,
        StoreManagerInterface $storeManager,
        RedirectFactory $redirectFactory,
        DataHelper $dataHelper
    ) {
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->redirectFactory = $redirectFactory;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Redirect to homepage when enable automatic redirect
     *
     * @param NorouteIndex $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function aroundExecute(NorouteIndex $subject, callable $proceed)
    {
        try {
            if ($this->dataHelper->isAutomaticRedirectEnabled()) {
                $redirect = $this->redirectFactory->create();
                $redirect->setPath($this->storeManager->getStore()->getBaseUrl());
                $redirect->setHttpResponseCode(DataHelper::PERMANENT_CODE);
                return $redirect;
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return $proceed();
    }
}
