<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Controller\Adminhtml\Sitemap;

use Amasty\XmlSitemap\Api\SitemapInterface;
use Magento\Framework\Phrase;

class MassDelete extends AbstractMassAction
{
    const ADMIN_RESOURCE = 'Amasty_XmlSitemap::sitemap';

    protected function itemAction(SitemapInterface $sitemap): void
    {
        $this->sitemapRepository->delete($sitemap);
    }

    protected function getErrorMessage(): Phrase
    {
        return __('We can\'t delete item right now. Please review the log and try again.');
    }

    protected function getSuccessMessage(int $collectionSize = 0): Phrase
    {
        if ($collectionSize) {

            return __('A total of %1 record(s) have been deleted.', $collectionSize);
        }

        return __('No records have been deleted.');
    }
}
