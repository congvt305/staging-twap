<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Source;

use Amasty\Blog\Model\XmlSitemap\Source\BlogEntitySource;
use Amasty\Faq\Model\XmlSitemap\Source\EntitySource as FaqEntitySource;
use Amasty\ShopbyBrand\Model\XmlSitemap\Source\Brand;
use Amasty\ShopbyPage\Model\XmlSitemap\Source\CustomPage;
use Amasty\Xlanding\Model\XmlSitemap\DataProvider\Landing;
use Amasty\XmlSitemap\Api\SitemapEntity\SitemapEntitySourceInterface;
use Amasty\XmlSitemap\Api\SitemapInterface;

class GenerateFilePath
{
    private const XML_SUFFIX = '.xml';

    /**
     * @param SitemapInterface $sitemap
     * @param SitemapEntitySourceInterface|BlogEntitySource|FaqEntitySource|Brand|CustomPage|Landing $source
     */
    public function execute(SitemapInterface $sitemap, $source): string
    {
        $entityFilename = $sitemap->getEntityData($source->getEntityCode())->getFilename()
            ?: $source->getEntityCode();
        if (strpos($entityFilename, self::XML_SUFFIX) === false) {
            $entityFilename .= self::XML_SUFFIX;
        }

        return str_replace(self::XML_SUFFIX, '_' . $entityFilename, $sitemap->getFilePath());
    }
}
