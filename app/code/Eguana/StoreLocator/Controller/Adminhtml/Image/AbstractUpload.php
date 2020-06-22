<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2016-09-05
 * Time: 오후 2:38
 */

namespace Eguana\StoreLocator\Controller\Adminhtml\Image;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Request\Http;

/**
 * image uploader abstract class
 *
 * Class AbstractUpload
 *  Eguana\StoreLocator\Controller\Adminhtml\Image
 */
abstract class AbstractUpload extends Action
{
    /**
     * @var UploaderFactory
     */
    protected $_imageUploader;

    /**
     * @var DirectoryList
     */
    protected $_directoryList;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * AbstractUpload constructor.
     * @param Context $context
     * @param DirectoryList $directoryList
     * @param UploaderFactory $imageUploader
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        DirectoryList $directoryList,
        UploaderFactory $imageUploader,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->_imageUploader = $imageUploader;
        $this->_storeManager = $storeManager;
        $this->_directoryList = $directoryList;
        $this->_logger = $logger;
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @return array
     */
    public function getUploaderInfo()
    {
        return [
            'area' => 'storeinfo_data',
            'name' => 'image'
        ];
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @return string
     */
    public function getImagePath()
    {
        return 'stores';
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
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
        } catch (Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    /**
     * get media path
     * @return string
     */
    public function getMediaPath()
    {
        $path = '';
        try {
            $path = $this->_directoryList->getPath('media');
        } catch (FileSystemException $e) {
            $this->_logger->error($e->getMessage());
        }
        return $path;
    }

    /**
     * get media url
     * @return mixed
     */
    public function getMediaUrl()
    {
        $url = null;
        try {
            $url = $this->_storeManager->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        } catch (NoSuchEntityException $e) {
            $this->_logger->error($e->getMessage());
        }
        return $url;
    }

    /**
     * get full path
     * @return string
     */
    public function getFullPath()
    {
        return $this->getMediaPath() . '/' . $this->getImagePath();
    }

    /**
     * get full purl
     * @return string
     */
    public function getFullUrl()
    {
        return $this->getMediaUrl() . $this->getImagePath() . '/';
    }

    /**
     * get file data
     * @return array
     */
    public function getFileData()
    {
        $uploaderInfo = $this->getUploaderInfo();
        $file = $this->_request->getFiles();
        return [
            'fileId' => [
                'tmp_name' => $file[$uploaderInfo['area']][$uploaderInfo['name']]['tmp_name'],
                'name' => $file[$uploaderInfo['area']][$uploaderInfo['name']]['name']
            ]
        ];
    }

    /**
     * upload file
     * @return array
     */
    public function fileUpload()
    {
        $path = null;
        $result = $this->_imageUploader->create($this->getFileData());
        $result->getUploadedFileName();
        $result->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
        try {
            $path = $result->save($this->getFullPath());
        } catch (Exception $e) {
            $this->_logger->error($e->getMessage());
        }
        return $path;
    }

    /**
     * return resulr set
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
