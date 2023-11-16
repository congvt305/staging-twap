<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Controller\Adminhtml\Sitemap;

use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Api\SitemapRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Delete extends Action implements HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'Amasty_XmlSitemap::sitemap';

    /**
     * @var SitemapRepositoryInterface $sitemapRepository
     */
    private $sitemapRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        SitemapRepositoryInterface $sitemapRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->sitemapRepository = $sitemapRepository;
        $this->logger = $logger;
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam(SitemapInterface::SITEMAP_ID);

        try {
            $this->sitemapRepository->deleteById($id);
            $this->messageManager->addSuccessMessage(__('Sitemap has been successfully deleted'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t delete item right now. Please review the log and try again.')
            );
            $this->logger->critical($e);
        }

        return $this->_redirect('*/*/');
    }
}
