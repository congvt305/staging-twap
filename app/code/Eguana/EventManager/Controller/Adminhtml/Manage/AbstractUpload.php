<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/6/20
 * Time: 7:46 PM
 */
namespace Eguana\EventManager\Controller\Adminhtml\Manage;

use Magento\Backend\App\Action\Context as Context;
use Magento\Framework\App\Filesystem\DirectoryList as DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\App\Action;
use Psr\Log\LoggerInterface;

/**
 * abstract class for uploading file
 * abstract Class AbstractUpload
 */
abstract class AbstractUpload extends Action
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Image uploader
     *
     * @var UploaderFactory $imageUploader
     */
    private $imageUploader;

    /**
     * @var string
     */
    private $imagePath = 'EventManager';
    /**
     * Directory List
     *
     * @var DirectoryList $directoryList
     */
    private $directoryList;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Upload constructor.
     *
     * @param Context $context
     * @param LoggerInterface $logger
     * @param DirectoryList $directoryList
     * @param StoreManagerInterface $storeManagerInterface
     * @param UploaderFactory $imageUploader
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        DirectoryList $directoryList,
        StoreManagerInterface $storeManagerInterface,
        UploaderFactory $imageUploader
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->imageUploader = $imageUploader;
        $this->directoryList = $directoryList;
        $this->storeManager = $storeManagerInterface;
    }

    /**
     * @return |null
     */
    public function getUploaderInfo()
    {
        return null;
    }

    /**
     * return image path
     * @return string
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }

    /**
     * return cookie
     * @return array
     */
    public function resultSetCookie()
    {
        return [
            'name' => $this->_getSession()->getName(),
            'value' => $this->_getSession()->getSessionId(),
            'lifetime' => $this->_getSession()->getCookieLifetime(),
            'path' => $this->_getSession()->getCookiePath(),
            'domain' => $this->_getSession()->getCookieDomain(),
        ];
    }

    /**
     * Upload file controller action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        try {
            $result = $this->fileUpload();
            $result['cookie'] = $this->resultSetCookie();
            $result = $this->returnResultSet($result);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    /**
     * return full media path for image
     * @return string|null
     */
    public function getFullPath()
    {
        try {
            return $this->directoryList->getPath(DirectoryList::MEDIA) . '/' . $this->getImagePath();
        } catch (FileSystemException $e) {
            return null;
        }
    }

    /**
     * return full media url for image
     * @return string|null
     */
    public function getFullUrl()
    {
        try {
            return $this->storeManager->getStore()->getBaseUrl('media') . $this->getImagePath() . '/';
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * return new filename for uploaded file
     * @param $fileName
     */
    public function getFileName($fileName)
    {
        try {
            $fileExtension = explode('.', $fileName)[count(explode('.', $fileName))-1];
            return md5_file($fileName . date('YmdHis') . random_int(1, 1000)) . '.' . $fileExtension;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * upload the file
     * @return array
     */
    public function fileUpload()
    {
        try {
            $result = $this->imageUploader->create(['fileId' => 'thumbnail_image']);
            $result->getUploadedFileName();
            $result->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            return $result->save($this->getFullPath());
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * return name and url of the file
     * @param $result
     * @return mixed
     */
    public function returnResultSet($result)
    {
        $result['name'] = $result['file'];
        $result['url'] = $this->getFullUrl() . $result['file'];
        return $result;
    }
}
