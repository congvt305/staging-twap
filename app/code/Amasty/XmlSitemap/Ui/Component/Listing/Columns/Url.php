<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Ui\Component\Listing\Columns;

use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Api\SitemapRepositoryInterface;
use Amasty\XmlSitemap\Model\Sitemap\SourceProvider;
use Amasty\XmlSitemap\Model\Sitemap\UrlProvider;
use Amasty\XmlSitemap\Model\Source\GenerateFilePath as GenerateEntityFilePath;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Url extends Column
{
    private const URL_FORMAT = '<a href="%1$s" target="_blank">%1$s</a>';

    /**
     * @var UrlProvider
     */
    private $urlProvider;

    /**
     * @var GenerateEntityFilePath
     */
    private $generateEntityFilePath;

    /**
     * @var SitemapRepositoryInterface
     */
    private $sitemapRepository;

    /**
     * @var SourceProvider
     */
    private $sourceProvider;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlProvider $urlProvider,
        GenerateEntityFilePath $generateEntityFilePath,
        SitemapRepositoryInterface $sitemapRepository,
        SourceProvider $sourceProvider,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->urlProvider = $urlProvider;
        $this->generateEntityFilePath = $generateEntityFilePath;
        $this->sitemapRepository = $sitemapRepository;
        $this->sourceProvider = $sourceProvider;
    }

    public function prepareDataSource(array $dataSource): array
    {
        foreach ($dataSource['data']['items'] as $key => $item) {
            $url = $this->urlProvider->getSitemapUrl($item['path'], (int)$item['store_id']);

            if ($url != null) {
                $dataSource['data']['items'][$key]['result_link'] = sprintf(self::URL_FORMAT, $url);
            } elseif ($indexFileUrl = $this->getIndexFileUrl($item['path'], (int) $item['store_id'])) {
                $storeId = (int) $item['store_id'];
                $html = sprintf(self::URL_FORMAT, $indexFileUrl) . '</br>';

                if ($item[SitemapInterface::IS_SEPARATE_ENTITY]) {
                    $urls = $this->getEntitiesFilesUrls(
                        (int) $item[SitemapInterface::SITEMAP_ID],
                        $storeId
                    );
                } else {
                    $urls = $this->getNumeratedFilesUrls($item['path'], $storeId);
                }

                foreach ($urls as $url) {
                    $html .= '-&nbsp;' . sprintf(self::URL_FORMAT, $url) . '</br>';
                }
                $dataSource['data']['items'][$key]['result_link'] = $html;
            } elseif ($urls = $this->getEntitiesFilesUrls(
                (int) $item[SitemapInterface::SITEMAP_ID],
                (int) $item['store_id']
            )) {
                $url = array_shift($urls);
                $dataSource['data']['items'][$key]['result_link'] = sprintf(self::URL_FORMAT, $url);
            } else {
                $dataSource['data']['items'][$key]['result_link'] = __('Not Generated Yet');
            }
        }

        return $dataSource;
    }

    private function getSitemap(int $sitemapId): SitemapInterface
    {
        return $this->sitemapRepository->getById($sitemapId);
    }

    private function getNumeratedFilesUrls(string $filePath, int $storeId): array
    {
        $num = 1;
        $result = [];

        while (true) {
            $numeratedFilename = $this->getNumeratedFileName($filePath, $num++);
            $url = $this->urlProvider->getSitemapUrl($numeratedFilename, $storeId);

            if ($url) {
                $result[] = $url;
            } else {

                break;
            }
        }

        return $result;
    }

    private function getIndexFileUrl(string $filePath, int $storeId): ?string
    {
        $indexFilePath = str_replace('.xml', '_index.xml', $filePath);
        return $this->urlProvider->getSitemapUrl($indexFilePath, $storeId);
    }

    private function getNumeratedFileName(string $fileName, int $num): string
    {
        return str_replace('.xml', sprintf('_%d.xml', $num), $fileName);
    }

    private function getEntitiesFilesUrls(int $sitemapId, int $storeId): array
    {
        $result = [];

        $sitemap = $this->getSitemap($sitemapId);
        foreach ($this->sourceProvider->getSourcesToGeneration($sitemap) as $source) {
            $entityFilePath = $this->generateEntityFilePath->execute($sitemap, $source);
            $entityFileUrl = $this->urlProvider->getSitemapUrl($entityFilePath, $storeId);
            if ($entityFileUrl) {
                $result[] = $entityFileUrl;
            }
            foreach ($this->getNumeratedFilesUrls($entityFilePath, $storeId) as $numeratedFileUrl) {
                $result[] = $numeratedFileUrl;
            }
        }

        return $result;
    }
}
