<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 5/1/21
 * Time: 6:00 PM
 */
declare(strict_types=1);

namespace Eguana\ImportCoupon\Model\ImportCsv;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read as ReadAlias;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * To upload csv file to dir
 *
 * Class FileUploader
 */
class FileUploader
{
    /**
     * @var string
     */
    const FILE_DIR = 'importCoupon/tmp';

    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     * @param UploaderFactory $uploaderFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Filesystem $filesystem,
        LoggerInterface $logger,
        UploaderFactory $uploaderFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->uploaderFactory = $uploaderFactory;
    }

    /**
     * Save file to temp media directory
     *
     * @param $fileId
     * @return array|bool|string[]
     */
    public function saveFileToMediaFolder($fileId)
    {
        try {
            $result = ['file' => '', 'size' => ''];
            /** @var ReadAlias $mediaDirectory */
            $mediaDirectory = $this->filesystem
                ->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath(self::FILE_DIR);
            $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $uploader->setAllowedExtensions(['csv']);
            $result = array_intersect_key($uploader->save($mediaDirectory), $result);
            $result['url'] = $this->storeManager
                    ->getStore()
                    ->getBaseUrl(
                        UrlInterface::URL_TYPE_MEDIA
                    ) . $this->getFilePath(self::FILE_DIR, $result['file']);
            $result['name'] = $result['file'];
            $allowedFileSize = 2;
            if (($result['size'] / 10) > ($allowedFileSize * 102400)) {
                $result = ['error' => 'Allow file size is ' . $allowedFileSize . ' MBs'];
                return $result;
            }
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $result;
    }

    /**
     * Retrieve path
     *
     * @param $path
     * @param $fileName
     * @return string
     */
    public function getFilePath($path, $fileName) : string
    {
        return rtrim($path, '/') . '/' . ltrim($fileName, '/');
    }

    /**
     * Get file url
     *
     * @param string $file
     * @return string
     */
    public function getMediaUrl(string $file) : string
    {
        $result = '';
        try {
            $file = ltrim(str_replace('\\', '/', $file), '/');
            $result = $this->storeManager
                    ->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . self::FILE_DIR . '/' . $file;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $result;
    }
}
