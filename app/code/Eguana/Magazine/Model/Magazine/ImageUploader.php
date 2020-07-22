<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/17/20
 * Time: 5:09 AM
 */
namespace Eguana\Magazine\Model\Magazine;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read as ReadAlias;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\Uploader as UploaderAlias;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Model class for file uploading
 * Class ImageUploader
 */
class ImageUploader
{
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
     * @var string
     */
    const FILE_DIR = 'Magazine';

    /**
     * @var LoggerInterface;
     */
    private $logger;

    /**
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }

    /**
     * Save file to temp media directory
     * @param $fileId
     * @return array|bool|string[]
     */
    public function saveImageToMediaFolder($fileId)
    {
        try {
            $result = ['file' => '', 'size' => ''];
            /** @var ReadAlias $mediaDirectory */
            $mediaDirectory = $this->filesystem
                ->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath(self::FILE_DIR);
            /** @var UploaderAlias $uploader */
            $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $uploader->setAllowedExtensions($this->getAllowedExtensions());
            $result = array_intersect_key($uploader->save($mediaDirectory), $result);
            $result['url'] = $this->getMediaUrl($result['file']);
        } catch (\Exception $e) {
            $this->logger->debug($exception->getMessage());
        }
        return $result;
    }

    /**
     * Get file url
     * @param $file
     * @return string
     */
    public function getMediaUrl($file)
    {
        try {
            $file = ltrim(str_replace('\\', '/', $file), '/');
            return $this->storeManager
                ->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . self::FILE_DIR . '/' . $file;
        } catch (\Exception $e) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * Get allowed file extensions
     *
     * @return string[]
     */
    public function getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png'];
    }
}
