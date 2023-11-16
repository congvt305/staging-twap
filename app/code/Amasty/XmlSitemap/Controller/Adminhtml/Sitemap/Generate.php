<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Controller\Adminhtml\Sitemap;

use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Api\SitemapRepositoryInterface;
use Amasty\XmlSitemap\Model\XmlGenerator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Generate extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Amasty_XmlSitemap::sitemap';

    /**
     * @var SitemapRepositoryInterface $sitemapRepository
     */
    private $sitemapRepository;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var XmlGenerator
     */
    private $xmlGenerator;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Context $context,
        SitemapRepositoryInterface $sitemapRepository,
        XmlGenerator $xmlGenerator,
        StoreManagerInterface $storeManager,
        Emulation $appEmulation,
        DateTime $dateTime,
        Registry $registry
    ) {
        parent::__construct($context);

        $this->sitemapRepository = $sitemapRepository;
        $this->xmlGenerator = $xmlGenerator;
        $this->appEmulation = $appEmulation;
        $this->_registry = $registry;
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
    }

    public function execute(): Redirect
    {
        $id = (int) $this->getRequest()->getParam(SitemapInterface::SITEMAP_ID);
        $redirect = $this->createRedirect('*/*/');

        try {
            $sitemap = $this->sitemapRepository->getById($id);
            if (!$sitemap->getId()) {
                $this->messageManager->addErrorMessage(__('Sitemap does not exist'));
                return $redirect;
            }

            $this->_registry->register(SitemapInterface::SITEMAP_GENERATION, true);
            $this->appEmulation->startEnvironmentEmulation($sitemap->getStoreId());

            $this->xmlGenerator->generate($sitemap);

            $this->appEmulation->stopEnvironmentEmulation();
            $sitemap->setLastGeneration($this->dateTime->gmtDate());
            $this->sitemapRepository->save($sitemap);

            $this->messageManager->addSuccessMessage(__('Sitemap has been generated'));
        } catch (\Exception $e) {
            $this->appEmulation->stopEnvironmentEmulation();
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $redirect;
    }

    private function createRedirect(string $path = ''): Redirect
    {
        $redirect = $this->resultRedirectFactory->create();

        return $redirect->setPath($path);
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Amasty_XmlSitemap::sitemap');
    }
}
