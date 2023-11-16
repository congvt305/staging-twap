<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Controller\Adminhtml\Sitemap;

use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Api\SitemapRepositoryInterface;
use Amasty\XmlSitemap\Model\Sitemap;
use Amasty\XmlSitemap\Model\Sitemap\Duplicate as DuplicateModel;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;

class Duplicate extends Action implements HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'Amasty_XmlSitemap::sitemap';

    /**
     * @var DuplicateModel
     */
    private $duplicate;

    public function __construct(
        Context $context,
        DuplicateModel $duplicate
    ) {
        parent::__construct($context);
        $this->duplicate = $duplicate;
    }

    public function execute()
    {
        $id = (int) $this->getRequest()->getParam(SitemapInterface::SITEMAP_ID);

        try {
            $this->duplicate->execute($id);
            $this->messageManager->addSuccessMessage(__('Sitemap was successfully duplicated'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->_redirect('*/*/index');
    }
}
