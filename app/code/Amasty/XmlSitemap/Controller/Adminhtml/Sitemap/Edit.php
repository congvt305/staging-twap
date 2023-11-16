<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Controller\Adminhtml\Sitemap;

use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Api\SitemapRepositoryInterface;
use Amasty\XmlSitemap\Model\SitemapFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

class Edit extends Action implements HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'Amasty_XmlSitemap::sitemap';

    /**
     * @var SitemapRepositoryInterface
     */
    private $sitemapRepository;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var Registry
     */
    private $coreRegistry;

    public function __construct(
        Context $context,
        SitemapRepositoryInterface $sitemapRepository,
        Escaper $escaper,
        Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->sitemapRepository = $sitemapRepository;
        $this->escaper = $escaper;
        $this->coreRegistry = $coreRegistry;
    }

    public function execute()
    {
        $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $id = (int)$this->getRequest()->getParam(SitemapInterface::SITEMAP_ID);

        if ($id) {
            try {
                $sitemap = $this->sitemapRepository->getById($id);
                $this->coreRegistry->register(SitemapInterface::PERSIST_NAME, $sitemap);
                $page->getConfig()->getTitle()->prepend(
                    __('Edit Sitemap "%1"', $this->escaper->escapeHtml($sitemap->getName()))
                );
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This sitemap no longer exists.'));

                return $this->_redirect('*/*/index');
            }
        } else {
            $page->getConfig()->getTitle()->prepend(__('Create New Sitemap'));
        }
        $page->setActiveMenu(Index::ADMIN_RESOURCE);

        return $page;
    }
}
