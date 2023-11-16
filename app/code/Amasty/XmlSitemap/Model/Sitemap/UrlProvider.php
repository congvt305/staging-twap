<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Sitemap;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class UrlProvider
{
    public const MEDIA_PART = 'pub/media/';

    /**
     * @var File
     */
    private $ioFile;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        File $ioFile,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->ioFile = $ioFile;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
    }

    public function getSitemapUrl(string $filePath, int $storeId): ?string
    {
        if ($filePath && $this->isFileExists($filePath)) {
            $url = $this->getCorrectUrl($filePath, $storeId);
        }

        return $url ?? null;
    }

    private function isFileExists(string $filePath): bool
    {
        $path = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath() . $filePath;

        return $this->ioFile->fileExists($path);
    }

    private function getCorrectUrl(string $filePath, int $storeId): string
    {
        $store = $this->storeManager->getStore($storeId);
        if (strpos($filePath, self::MEDIA_PART) !== false) {
            $baseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $filePath = str_replace(self::MEDIA_PART, '', $filePath);
            $filePath = ltrim($filePath, '/');
        } else {
            $baseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_DIRECT_LINK);
        }

        return $baseUrl . $filePath;
    }
}
