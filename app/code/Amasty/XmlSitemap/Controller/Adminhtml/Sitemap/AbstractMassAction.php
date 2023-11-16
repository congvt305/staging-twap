<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Controller\Adminhtml\Sitemap;

use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Model\Repository\SitemapRepository;
use Amasty\XmlSitemap\Model\ResourceModel\Sitemap\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Phrase;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

abstract class AbstractMassAction extends Action implements HttpPostActionInterface
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var SitemapRepository
     */
    protected $sitemapRepository;

    public function __construct(
        Context $context,
        Filter $filter,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        SitemapRepository $sitemapRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->sitemapRepository = $sitemapRepository;
    }

    abstract protected function itemAction(SitemapInterface $sitemap): void;

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        try {
            $collectionSize = $collection->getSize();
            if ($collectionSize) {
                foreach ($collection as $sitemap) {
                    $this->itemAction($sitemap);
                }
            }

            $this->messageManager->addSuccessMessage($this->getSuccessMessage($collectionSize));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($this->getErrorMessage());
            $this->logger->critical($e);
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }

    protected function getErrorMessage(): Phrase
    {
        return __('We can\'t change item right now. Please review the log and try again.');
    }

    protected function getSuccessMessage(int $collectionSize = 0): Phrase
    {
        return $collectionSize
            ? __('A total of %1 record(s) have been changed.', $collectionSize)
            : __('No records have been changed.');
    }
}
