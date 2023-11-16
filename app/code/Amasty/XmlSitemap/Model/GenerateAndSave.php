<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model;

use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Api\SitemapRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\App\Emulation;

class GenerateAndSave
{
    /**
     * @var XmlGenerator
     */
    private $xmlGenerator;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var SitemapRepositoryInterface
     */
    private $sitemapRepository;

    public function __construct(
        XmlGenerator $xmlGenerator,
        Emulation $appEmulation,
        DateTime $dateTime,
        SitemapRepositoryInterface $sitemapRepository
    ) {
        $this->xmlGenerator = $xmlGenerator;
        $this->appEmulation = $appEmulation;
        $this->dateTime = $dateTime;
        $this->sitemapRepository = $sitemapRepository;
    }

    public function execute(SitemapInterface $sitemap): void
    {
        $this->appEmulation->startEnvironmentEmulation($sitemap->getStoreId());
        $this->xmlGenerator->generate($sitemap);
        $this->appEmulation->stopEnvironmentEmulation();

        $sitemap->setLastGeneration($this->dateTime->gmtDate());
        $this->sitemapRepository->save($sitemap);
    }
}
