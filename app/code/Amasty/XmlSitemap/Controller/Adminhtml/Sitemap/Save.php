<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Controller\Adminhtml\Sitemap;

use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Api\SitemapRepositoryInterface;
use Amasty\XmlSitemap\Model\SitemapFactory;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Amasty_XmlSitemap::sitemap';

    /**
     * @var SitemapRepositoryInterface
     */
    private $sitemapRepository;

    /**
     * @var SitemapFactory
     */
    private $sitemapFactory;

    public function __construct(
        Action\Context $context,
        SitemapFactory $sitemapFactory,
        SitemapRepositoryInterface $sitemapRepository
    ) {
        parent::__construct($context);
        $this->sitemapRepository = $sitemapRepository;
        $this->sitemapFactory = $sitemapFactory;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            if (!isset($data['additional']['entities'])) {
                $data['additional']['entities'] = [];
            }
            $id = (int)$this->getRequest()->getParam(SitemapInterface::SITEMAP_ID);

            try {
                $sitemap = $this->sitemapRepository->getById($id);
            } catch (NoSuchEntityException $exception) {
                $sitemap = $this->sitemapFactory->create();
            }
            $sitemap->setData($data);

            try {
                $this->sitemapRepository->save($sitemap);
                $this->messageManager->addSuccessMessage(__('Sitemap was successfully saved'));
                $this->_session->setFormData(false);

                if ($this->getRequest()->getParam('back')) {

                    return $this->_redirect('*/*/edit', [SitemapInterface::SITEMAP_ID => $sitemap->getSitemapId()]);
                }

                return $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_session->setFormData($data);

                return $this->_redirect('*/*/edit', [SitemapInterface::SITEMAP_ID => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find item to save'));

        return $this->_redirect('*/*/');
    }
}
