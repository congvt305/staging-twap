<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Controller\Adminhtml\Sitemap;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'Amasty_XmlSitemap::sitemap';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $pageResult = $this->resultPageFactory->create();

        $pageResult->setActiveMenu(self::ADMIN_RESOURCE);
        $pageResult->addBreadcrumb(__('Manage Google XML Sitemaps'), __('Manage Google XML Sitemaps'));
        $pageResult->getConfig()->getTitle()->prepend(__('Manage Google XML Sitemaps '));

        return $pageResult;
    }
}
