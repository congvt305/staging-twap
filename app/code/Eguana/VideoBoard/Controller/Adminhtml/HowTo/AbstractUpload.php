<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 18/6/20
 * Time: 6:58 PM
 */
namespace Eguana\VideoBoard\Controller\Adminhtml\HowTo;

use Magento\Backend\App\Action\Context as Context;
use Magento\Framework\App\Filesystem\DirectoryList as DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\App\Action;

/**
 * abstract class for uploading file
 *
 * abstract Class AbstractUpload
 */

abstract class AbstractUpload extends Action
{
    /**
     * Image uploader
     *
     * @var UploaderFactory $imageUploader
     */
    protected $imageUploader;

    /**
     * @var string
     */
    protected $imagePath = 'VideoBoard';
    /**
     * Directory List
     *
     * @var DirectoryList $_directoryList
     */
    protected $_directoryList;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Upload constructor.
     *
     * @param Context $context
     * @param DirectoryList $directoryList
     * @param StoreManagerInterface $storeManagerInterface
     * @param UploaderFactory $imageUploader
     */
    public function __construct(
        Context $context,
        DirectoryList $directoryList,
        StoreManagerInterface $storeManagerInterface,
        UploaderFactory $imageUploader
    ) {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
        $this->_directoryList = $directoryList;
        $this->_storeManager = $storeManagerInterface;
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
            return $this->_directoryList->getPath(DirectoryList::MEDIA) . '/' . $this->getImagePath();
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
            return $this->_storeManager->getStore()->getBaseUrl('media') . $this->getImagePath() . '/';
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * return new filename for uploaded file
     * @param $fileName
     * @return string
     * @throws \Exception
     */
    public function getFileName($fileName)
    {
        $fileExtension = explode('.', $fileName)[count(explode('.', $fileName))-1];
        return md5_file($fileName . date('YmdHis') . random_int(1, 1000)) . '.' . $fileExtension;
    }

    /**
     * upload the file
     * @return array
     * @throws \Exception
     */
    public function fileUpload()
    {
        $result = $this->imageUploader->create(['fileId' => 'thumbnail_image']);
        $result->getUploadedFileName();
        $result->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
        return $result->save($this->getFullPath());
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
